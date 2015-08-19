<?php

// Copyright (c) 2013, Carlos Cesar Peña Gomez <CarlosCesar110988@gmail.com>
//
// Permission to use, copy, modify, and/or distribute this software for any 
// purpose with or without fee is hereby granted, provided that the above copyright 
// notice and this permission notice appear in all copies.

// THE SOFTWARE IS PROVIDED 'AS IS' AND THE AUTHOR DISCLAIMS ALL 
// WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED 
// WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL 
// THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL 
// DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA 
// OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR 
// OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE 
// USE OR PERFORMANCE OF THIS SOFTWARE.

function conektabanorte_config() {
    $configarray = array(
		'FriendlyName' => array(
			'Type' =>'System', 
			'Value' =>'Conekta Banorte'
		),
		'private_key' => array(
			'FriendlyName' => 'Llave Privada', 
			'Type' => 'text', 
			'Size' => '50'
		),
		'instructions' => array(
			'FriendlyName' => 'Instrucciones de pago', 
			'Type' => 'textarea', 
			'Rows' => '5', 
			'Description' => ''
		)
    );
	return $configarray;
}


function conektabanorte_link($params) {

	# Variables de Conekta
	$private_key = $params['private_key'];

    # Variables de la Factura
	$invoiceid = $params['invoiceid'];
	$amount = $params['amount'];
    $currency = $params['currency'];

    # Variables del cliente
	$firstname = $params['clientdetails']['firstname'];
	$lastname = $params['clientdetails']['lastname'];
	$email = $params['clientdetails']['email'];
	$address1 = $params['clientdetails']['address1'];
	$address2 = $params['clientdetails']['address2'];
	$city = $params['clientdetails']['city'];
	$state = $params['clientdetails']['state'];
	$postcode = $params['clientdetails']['postcode'];
	$country = $params['clientdetails']['country'];
	$phone = $params['clientdetails']['phonenumber'];


	
	$results = array();
	
	# Preparamos todos los parametros para enviar a Conekta.io
	
	$data_amount = str_replace('.', '', $amount);
	$data_currency = strtolower($currency);
	$data_description = 'Pago Factura No. '.$invoiceid;
	
	# Incluimos la libreria de Conecta

	require_once('conekta/lib/Conekta.php');
	
	# Creamos el Objeto de Cargo
	Conekta::setApiKey($private_key);
	
	# Arraglo con informacion de tarjeta
	$conekta = array(
				'description' => $data_description, 
				'reference_id' => 'factura_'.$invoiceid, 
				'amount' => intval($data_amount), 
				'currency' => $data_currency, 
				'bank' => array('type'=>'banorte')
				'details'=> array(
									      'email'			=> $email,
									      'line_items'		=> array(
															        array( 
															          'name'		=>	$data_description,
															          'sku'			=>	$invoiceid,
															          'unit_price'	=> 	intval($data_amount),
															          'description'	=>	$data_description,
															          'quantity'	=> 	1,
															          'type'		=>	'service-purchase'
															        )
									      )
									      
									 )
				);
	try {
	
	
	  $charge = Conekta_Charge::create($conekta);
	  
	  # Transaccion Correcta
	  $data = json_decode($charge);
	  
	  $service_name = $data->payment_method->service_name;
	  $service_number = $data->payment_method->service_number;
	  $type =  $data->payment_method->type;
	  $reference =  $data->payment_method->reference;

	  
	  $ticket = 1;
	 
	} 
	
	catch (Exception $e) 
	{
	  $code =  "Error al intentar generar pago en OXXO";
	  
	  $ticket = 0;
	}

	
	if($ticket==1)
	{
		$code = '<form action="conekta_banorte.php" method="post" target="_blank">';
		$code .= '<input type="hidden" name="service_name" value="'. $service_name .'" />';
		$code .= '<input type="hidden" name="service_number" value="'. $service_number .'" />';
		$code .= '<input type="hidden" name="reference" value="'. $reference .'" />';
		$code .= '<input type="hidden" name="monto" value="'. $amount .'" />';
		$code .= '<input type="hidden" name="concepto" value="'. $data_description .'" />';
		$code .= '<input type="submit" value="Pagar ahora" />';
		$code .= '</form>';
	}
	
	return $code;

}

?>