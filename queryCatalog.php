<?php
  header('Content-type: application/json');

  $xml = simplexml_load_string(
    file_get_contents(
       'http://testbedapps.sura.org/gi-cat/services/cswiso'
      ,false
      ,stream_context_create(array('http' => array(
         'method'  => 'POST'
        ,'header'  => 'Content-type: text/xml'
        ,'content' => str_replace('__TITLE__',$_REQUEST['t'],file_get_contents('post_template.xml'))
      )))
    )
  );

  $cswNs = 'http://www.opengis.net/cat/csw/2.0.2';
  $gmdNs = 'http://www.isotc211.org/2005/gmd';
  $srvNs = 'http://www.isotc211.org/2005/srv';
  $gcoNs = 'http://www.isotc211.org/2005/gco';

  $d = array();
  foreach ($xml->children($cswNs)->{'SearchResults'} as $searchResults) {
    foreach ($searchResults->children($gmdNs)->{'MD_Metadata'} as $mdMetadata) {
      $m = array();
      foreach ($mdMetadata->children($gmdNs)->{'identificationInfo'} as $identificationInfo) {
        foreach ($identificationInfo->children($gmdNs)->{'MD_DataIdentification'} as $dataIdentification) {
          $m['title'] = sprintf("%s",$dataIdentification->children($gmdNs)->{'citation'}[0]->children($gmdNs)->{'CI_Citation'}[0]->children($gmdNs)->{'title'}[0]->children($gcoNs)->{'CharacterString'});
        }
        foreach ($identificationInfo->children($srvNs)->{'SV_ServiceIdentification'} as $serviceIdentification) {
          if (sprintf("%s",$serviceIdentification->attributes()->{'id'}) == 'OGC-SOS') {
            $m['sosGetCaps'] = sprintf("%s",$serviceIdentification->children($srvNs)->{'containsOperations'}[0]->children($srvNs)->{'SV_OperationMetadata'}->children($srvNs)->{'connectPoint'}->children($gmdNs)->{'CI_OnlineResource'}->children($gmdNs)->{'linkage'}->children($gmdNs)->{'URL'});
          }
        } 
      }
      array_push($d,$m);
    }
  }
  echo json_encode($d);
?>