<?php

// function
function getOrderDetails($order){	
	
	$order = (int) $order;
	if(empty($order)) return false;
	
	global $db;
	
	$qry = "
		SELECT d.*, o.sconto_listino, o.sconto_cliente, o.anno, o.data, o.spedizione, o.agente, o.stato, o.salvato, 
		p.nome AS prodotto, v.nome AS variante 
		FROM `data_ordini_dettagli` AS d 
		JOIN `data_ordini` AS o ON(o.id = d.ordine) 
		JOIN `data_prodotti` AS p ON (p.id = d.articolo) 
		LEFT JOIN `data_varianti` AS v ON (v.id = d.variante)
		WHERE d.ordine = '".$order."'	
	";
	
	return $db->fetch_array($qry);
	
}


function getOrderInfo($order){
	
	$order = (int) $order;
	if(empty($order)) return false;
	
	global $db;
	
	$qry = "
		SELECT o.anno, o.progressivo, o.data, o.cliente AS cid, 
		c.rag_soc AS cliente, 
		d.nome_sede AS destinazione_nome, d.indirizzo AS destinazione_indirizzo, 
		d.cap AS destinazione_cap, d.prov AS destinazione_prov, 
		o.sconto_listino, o.sconto_cliente, p.nome AS pagamento,
		s.nome AS spedizione, 
		CONCAT(a.name, ' ', a.surname) AS agente, o.stato, o.stato_precedente, o.salvato,
		o.data_annullato, o.note, o.note_spedizione, o.insertedby, o.updatedby, o.ts
		FROM `data_ordini` AS o  
		JOIN `data_clienti` AS c ON (c.id = o.cliente) 
		LEFT JOIN `data_clienti_sedi` AS d ON (d.id = o.destinazione)
		LEFT JOIN `data_forme_pagamento` AS p ON (p.id = o.forma_pagamento)
		LEFT JOIN `data_spedizioni` AS s ON (s.id = o.spedizione)
		LEFT JOIN `users` AS a ON (a.id = o.agente)
		WHERE o.id = '".$order."'	
	";
	
	return $db->fetch_array($qry);

		
	
	
}

?>