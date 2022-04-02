<?php
/**
 * Created by PhpStorm.
 * User: Anatoly
 * Date: 18.07.2018
 * Time: 13:16
 */

class Export extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('tank_auth');
        $this->load->model('tenders_data', 'tenders');
    }

    public function tag()
    {
        $all_tags = $this->tenders->get_all_tender_tags('id');

        $xml = new SimpleXMLElement('<xml/>');
		if(is_array($all_tags)){
			foreach ($all_tags as $tag){
				$track = $xml->addChild('tag');
				$track->addChild('id', $tag['id']);
				$track->addChild('caption', $tag['caption']);
			}
		}
        Header('Content-type: text/xml');
        print($xml->asXML());
    }
}
