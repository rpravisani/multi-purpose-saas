<?php
// TODO : Ragruppare qua parte (+/-) generico del menu header e laterale
?>
<div id="top-section">
  <div id="header">
  	<div class="sx" id="left-header">
    	<!--<img src="images/logo.png" width="344" height="100" />-->
       <h1><?php echo NOME_DITTA_ADMIN ?></h1>
    </div>
    <div class="dx" id="right-header">
		<p>Benvenuto <strong><?php echo $utente->getNome(); ?></strong>&nbsp;|&nbsp;<span id="clock"></span>&nbsp;|&nbsp;<a href='logout.php'><img src="images/exit.png" class="btn" width="12" height="14" title="Scollegati" alt="Exit" /></a></p>    
    </div>
    <br class="clear" />
  </div>
  
  <div id="menu">
    <?php
	echo $menu->getMenu();
  	?>
    <br class="clear" />
  </div>
  
  <div id="statusbar">
  	<div id="nome-modulo" class="sx">
    	<?php
		$iconastatus = $menu->getActiveItem("icon");
		if($iconastatus) echo "<img class='sx' src='images/".$iconastatus."' width='18' height='18'>";
		?>
    	<p title="<?php echo strip_tags($description); ?>" class='sx'><?php echo $title; ?></p>
        <br class="clear" />
    </div>
    
    <?php

	if($params['filtri']){
	?>
    <div id="portafiltro" class="dx">
    <form>
      <?php
		foreach($params['filtri'] as $nome_filtro => $valori_filtro){
		?>
      <label for="filtro_<?php echo $nome_filtro; ?>"><?php echo ucfirst($nome_filtro); ?></label>
      
      <select name="filtro_<?php echo $nome_filtro; ?>" id="filtro_<?php echo $nome_filtro; ?>" onchange="cambiaFiltro(this)" >
        <?php
			
			foreach($valori_filtro as $valore_opzione => $opzione){
				$selected = "";
				$def = ( $params['default_filter_value'] ) ? $params['default_filter_value'] : "0" ;
				if(!$pf[$nome_filtro] and $def == $valore_opzione){
					$selected = "selected='selected'";
				}else if(!empty($pf[$nome_filtro]) and $pf[$nome_filtro] == $valore_opzione){
					$selected = "selected='selected'";
				}
				
			?>
        <option value="<?php echo $valore_opzione; ?>" <?php echo $selected; ?>><?php echo $opzione; ?></option>
        <?php
			}
			?>
      </select>
      
      <?php
		}
		?>
        </form>
    </div>
    <?php
	}
	?>
    
    <?php if($gotNextBtn){ ?>
    <div title="Record Successivo" id="next" class="sfoglia active dx">>></div>
    <div title="Record Precedente" id="previous" class="sfoglia active dx"><<</div>
    
    <?php } ?>
    
    <br class="clear" />
   </div>
</div>

<div id="sidebar">
	<?php
	if(!empty($sidebarmenu)){
		foreach($sidebarmenu as $k=>$v){
	?>
	<div class="sidebutton" id="<?php echo $k; ?>">
    <img src="<?php echo PATH_IMMAGINI.$k.".png"; ?>" title="<?php echo $v; ?>" />
    </div>
    <?php
		}
	}
	?>
</div>
