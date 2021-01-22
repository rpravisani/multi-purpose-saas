<?php
/******************************
 * INCLUDED IN EXPORT2CSV.PHP *
   USED FOR WOOCOMMERCE IMPORT
 ******************************/

$separator = ";";

// HELPERS
$imgurl = HTTP_PROTOCOL.HOSTROOT.SITEROOT."photo/";
$unita_variante = "misura";

//GET DATA

$qry_prodotti = "
SELECT 
p.nome AS 'post_title', 
'' AS 'post_excerpt', 
'' AS 'post_content', 
'publish' AS 'post_status',
'closed' AS 'comment_status',
p.sku, 
'instock' AS 'stock_status', 
p.prezzo_vendita AS 'regular_price', 
'taxable' AS 'tax_status', 
CONCAT('".$imgurl."', m.`file`) AS 'images', 
CASE
    WHEN p.varianti = 0 THEN 'simple'
    ELSE 'variable'
END AS 'tax:product_type', 
c.nome AS 'tax:product_cat', 
'' AS 'tax:product_tag', 
GROUP_CONCAT(v.nome SEPARATOR '|') AS 'attribute:pa_".$unita_variante."', 
'0|0|1' AS 'attribute_data:pa_".$unita_variante."', 
'' AS 'attribute_default:pa_".$unita_variante."'
FROM `data_prodotti` AS p 
JOIN `data_categorie` AS c ON (c.id = p.categoria) 
LEFT JOIN `media` AS m ON (m.record = p.id AND m.order = '1' AND m.page = '15') 
LEFT JOIN `data_varianti_x_gruppi` AS x ON (x.gruppo = p.varianti) 
LEFT JOIN data_varianti as v ON (v.id = x.variante) 
WHERE p.active = '1' 
GROUP BY p.id 
ORDER BY p.sku
";

$data['prodotti'] = $db->fetch_array($qry_prodotti); // key is filename


/*** VARIANTI ***/

$qry_varianti = "
SELECT 
p.nome AS 'Parent', 
p.sku AS 'parent_sku',
'publish' AS 'post_status',
v.id AS 'menu_order',
'instock' AS 'stock_status', 
pv.prezzo as 'regular_price', 
'parent' AS 'tax_class', 
'no' AS 'meta:_backorders', 
'no' AS 'meta:_manage_stock', 
'no' AS 'meta:_sold_individually', 
'taxable' AS 'meta:_tax_status', 
REPLACE( v.nome, ' ', '-' ) as 'meta:attribute_pa_".$unita_variante."'
FROM `data_prodotti` AS p 
JOIN `data_varianti_x_gruppi` AS x ON (x.gruppo = p.varianti) 
JOIN `data_prodotti_varianti` AS pv ON (pv.prodotto = p.id and pv.variante = x.variante) 
JOIN data_varianti as v ON (v.id = pv.variante) 
WHERE p.active = '1' AND pv.listino = '1' AND pv.active = '1'
ORDER BY p.sku, v.id
";

$data['varianti'] = $db->fetch_array($qry_varianti); // key is filename



?>