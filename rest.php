<?php

class Rest
{
	var $CI;
	var $_formats = array(
					'xml' 		=> 'application/xml',
					'json' 		=> 'application/json'
					);
	
	function __construct()
	{
		$this->CI =& get_instance();
	}
	
	function request($url, $method = "GET", $data = NULL)
	{
		if($url === NULL) { $url = $this->server . $this->uri; }

		switch($method)
		{
			case "GET":
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data = curl_exec($ch);
				curl_close($ch);
				
				echo($data);

				break;

			case "PUT":
				
				if($data === NULL)
				{
					$response = array('error' => 'You cannot perform a POST request with no data!');
					$this->response($response);
				}
				else
				{
					$fields_string = "";
					
					foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
					rtrim($fields_string,'&');
					
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "PUT");
					curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
					$data = curl_exec($ch);
					curl_close($ch);
					
					echo($data);
				}
				
				break;

			case "POST":
			
				if($data === NULL)
				{
					$response = "You cannot perform a POST request with no data!";
					$this->response($response);
				}
				else
				{
					$fields_string = "";
					
					foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
					rtrim($fields_string,'&');

					
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch,CURLOPT_POST,count($data));
					curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
					$data = curl_exec($ch);
					curl_close($ch);
					
					echo($data);
				}
				
				break;

			case "DELETE":
				
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data = curl_exec($ch);
				curl_close($ch);

				echo($data);
				
				break;
		}
	}
	
	function response($data, $http_status = 200, $format = 'json')
	{
		if(empty($data))
    	{
    		$this->output->set_status_header(404);
    		return;
    	}
	    
    	$this->output->set_status_header($http_status);

        if(method_exists($this, '_format_'.$format))
        {
	    	$this->output->set_header('Content-type: ' . $this->_formats[$format]);
    	
        	$final_data = $this->{'_format_'.$format}($data);
        	$this->output->set_output($final_data);
        }
        else
		{
        	$this->output->set_output($data);
        }
	}
	
	
	function _format_xml($data)
	{
		$xml = new XmlWriter();
		$xml->openMemory();
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement('root');

		function write(XMLWriter $xml, $data)
		{
			foreach($data as $key => $value)
			{
				if(is_array($value))
				{
					if(is_numeric($key))
					{
						$key = 'id';
					}
					$xml->startElement($key);
					write($xml, $value);
					$xml->endElement();
					continue;
				}
				$xml->writeElement($key, $value);
			}
		}
		write($xml, $data);

		$xml->endElement();
		echo $xml->outputMemory(true);
	}
	
	function _format_json($data)
	{
		return json_encode($data);
	}
}

?>
