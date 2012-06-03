<?php

class Rest_controller extends CI_Controller 
{
	
	var $request_method;
	var $id;
	var $method;
	var $_formats = array(
						'xml' 		=> 'application/xml',
						'json' 		=> 'application/json'
	);
	
	function __construct()
	{
		parent::__construct();
		
		$this->request_method = $_SERVER['REQUEST_METHOD'];
		$this->id = (int) $this->uri->segment("3");
		$this->method = $this->uri->segment("2");
	}
	
	function _remap()
	{	
		if($this->method != "index")
		{
			if($this->id == NULL)
			{
				switch($this->request_method)
				{
					case "GET":
						$this->{$this->method . "_get"}();
						break;

					case "POST":
						$this->{$this->method . "_post"}();
						break;

					case "DELETE":
						$this->{$this->method . "_delete"}();
						break;
				}
			}
			else
			{
				switch($this->request_method)
				{
					
					case "GET":
						$this->{$this->method . "_get"}($this->id);
						break;

					case "PUT":
						$this->{$this->method . "_put"}($this->id);
						break;

					case "DELETE":
						$this->{$this->method . "_delete"}($this->id);
						break;
				}
			}
		}
		else
		{
			$this->index();
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
