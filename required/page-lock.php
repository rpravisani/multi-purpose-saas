<?php
class page_locks{
    
    private $pid, $record, $user;
    //private $max_timeout = SESSION_LENGHT * 60;
    private $max_timeout = 60;
    
    public function __construct($pid, $record, $user){
        
        if(empty($pid)) die("Nessun page id passato");
        //if(empty($record)) die("Nessun record id passato");
        if(empty($user)) die("Nessun user id passato");
        
        // localize vars
        $this->pid = (int) $pid;
        $this->record = (int) $record;
        $this->user = (int) $user;
        
        return true;
        
    }
    
    /**
     * Controlla se l'attuale pagina è bloccabile
     * Verifica che sia di tipo module o custom e che non cia dashboard e che non sia un system page
     *
     * @return true | false
     */
    public function isBloccable(){
        global $page;
        
        return ( in_array($page['type'], array("custom", "module") ) and $page['home'] == '0' and $page['system_page'] == '0' ) ? true : false;
        
    }
    
    /**
     * Elimino tutti i miei record in page_locks che non corrispondono all'attuale pid e record
     *
     * @return true or false
     */
    public function delete_non_related_page_locks(){

        global $db;
        return $db->delete("page_locks", "WHERE user = '".$this->user."' AND pid != '".$this->pid."' AND record != '".$this->record."'");

    }
    
    /**
     * Elimino tutti i record in page_locks che sono scaduti o che sono miei ma che non corrispondono all'attuale pid e record
     *
     * @return true or false
     */
    public function delete_overdue_locks(){

        global $db;
        
        $now = new DateTime($this->getCurrentTimeStamp());
        $now->modify("-".$this->max_timeout." seconds");
        
        $where = "WHERE ts < '".$now->format("Y-m-d H:i:s")."'";
        $where .= " OR (user = '".$this->user."' AND pid != '".$this->pid."' AND record != '".$this->record."')";
        
        return $db->delete("page_locks", $where);

    }
    
    /**
     * Recupero user id dell'utente che sta bloccando il record
     *
     * @return (int) user id | (bool) false se non c'è alcun utente che blocca il record
     */
    public function get_locking_user(){
        
        global $db;
                
        return $db->get1value("user", "page_locks", "WHERE pid = '".$this->pid."' AND record = '".$this->record."'");
        
    }
    
    /**
     * Blocco il record con la mia utenza. Se vi sono altri blocchi li eliminio preliminariamente poiché ne può esistere solo uno di lock per pagina / record
     *
     * @return (int) insert id
     */
    public function block(){
        
        global $db;
                
        // Rimuovo eventuali altri lock sulla pagina 
        if(!$db->delete("page_locks", "WHERE pid = '".$this->pid."' AND record = '".$this->record."'")){
            $_SESSION['error_title']   = "Impossibile sbloccare record da altro utente!";
            $_SESSION['error_message'] = "Riscontrato errore durante la cancellazione del recrod lock di un precedente utente";
            return false;
        }
        
        $fields = array("pid", "record", "user");
        $values = array($this->pid, $this->record, $this->user);
        
        if(!$db->insert("page_locks", $values, $fields )){
            $_SESSION['error_title']   = "Impossibile bloccare record !";
            $_SESSION['error_message'] = "Riscontrato errore durante inseriemnto nuovo page lock";
            return false;
        }
        
        return $db->get_insert_id();
        
    }
    
    /**
     * Aggiorno il campo timestamp con CURRENT_TIMESTAMP del server DB per evitare errori per disallineamento tra server apache e server mySql
     * In caso update non va a buon fine registro messaggio di warning e restituisco false.
     *
     * @return (bool) false se update fallisce, se no true
     */
    public function updateTimestamp(){
        
        global $db;
                
        $qry = "UPDATE `page_locks` SET ts = CURRENT_TIMESTAMP WHERE user = '".$this->user."' AND pid != '".$this->pid."' AND record != '".$this->record."'";
        
        if(!$db->execute_query($qry)){
            $_SESSION['page_message_title'] = "Page lock update failure!";
            $_SESSION['page_message_message'] = "Impossibile aggiornare il timestamp del page lock";
            return false;
        }
        
        return true;
        
    }
    
    
    /**
     * Controllo se l'utente che ha bloccato il record è ancora attivo. 
     * Calcolo il tempo da ultimo update del page lock record, ovvero dall'ultimo refresh.
     * Tempo massimo intervento max_timeout corrisponde a SESSION_LENGHT * 60, ovvero il tempo di scadenza sessione su framework in secondi
     * In caso update non va a buon fine registro messaggio di warning e restituisco false.
     *
     * @return (bool) false se tempo da ultimo update è maggiore di max_timout, true se non lo è.
     */
    public function stillActive(){
        
        global $db;
                
        $last_update = $db->get1value("ts", "page_locks", "WHERE pid != '".$this->pid."' AND record != '".$this->record."'");
        
        $dt = new DateTime($last_update);
        $now = new DateTime($this->getCurrentTimeStamp());
        
        $interval = $now->format('U')-$dt->format('U');
        
        // return true or false
        return $interval < $this->max_timeout;
        
    }
    
    
    /**
     * Recupero timestamp attuale del server MySql
     *
     * @return (string) timestamp in formato aaaa-mm-gg hh:ii:ss
     */
    private function getCurrentTimeStamp(){
        global $db;
        
        $qry = "SELECT CURRENT_TIMESTAMP AS ts";
        $row = $db->fetch_array_row($qry);
        return $row['ts'];
    }
    
}


// Creo istanza della classe page_locks
$page_locks = new page_locks($pid, $_record, $_user->getUserId());

//Elimino tutti i record in page_locks che sono scaduti o che sono miei, ma che non corrispondono all'attuale pid e record (sono in poche parole uscito dal modulo)
$_page_lock_deleted = $page_locks->delete_overdue_locks();

// Sono in un contesto di modulo o custom che presume quindi un unico utente che modifica il record
if( $page_locks->isBloccable() ){
    
    // Recupero eventuale utente che sta attualmente bloccando il record
    $_page_lock_user = $page_locks->get_locking_user();
    
    if($_page_lock_user === false ){
        // nessun sta bloccando il record lo blocco io
        $page_locks->block(); // per evitare doppioni il metodo include la cancellazione di eventuali lock da parte di altri utenti
    }else if($_page_lock_user == $_user->getUserId()){
        // Sto bloccando io il record - aggiorno il timestamp
        $page_locks->updateTimestamp();
    }else{
        // Lo sta bloccando un altro utente...
        if($page_locks->stillActive()){
            // Se l'utente precedente è ancora attivo ridiriggo me stesso verso l'elenco
            $_SESSION['error_title']   = "Record non editabile!";
            $_SESSION['error_message'] = "Il record è attualmente in uso da un altro utente, prego ritentare dopo.";
            header( 'location: ' . HTTP_PROTOCOL . HOSTROOT . SITEROOT . "cpanel.php?pid=".$_modpid."&v=html" );
            exit;
        }else{
            // butto fuori utente precedente e prendo il controllo io
            $page_locks->block();
            
        }
    }
    
}
