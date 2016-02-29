<?php
/**
 * @file
 * Contains \Drupal\cmmc_reflection\CMMCReflectionController.
 */

namespace Drupal\cmmc_reflection; 

use Drupal\Core\Controller\ControllerBase; 

use \Drupal\node\Entity\Node;
use \Drupal\field\FieldConfigInterface;
use Symfony\Component\HttpFoundation\Response;

define(NOT_IN_ARRAY, false);
define(IN_ARRAY, true);

class CMMCReflectionController extends ControllerBase { 
  const NODE_SAVE_FAILED = "NODE_SAVE_FAILED";
  const CONTENT_TYPE_NOT_FOUND = "CONTENT_TYPE_NOT_FOUND";

  public function contentTypeFields($contentType) {
      $entityManager = \Drupal::service('entity.manager');
      $fields = [];
  
      if(!empty($contentType)) {
          $fields = array_filter(
              $entityManager->getFieldDefinitions('node', $contentType), function ($field_definition) {
                  return $field_definition instanceof FieldConfigInterface;
              }
          );
      }
  
      return $fields;      
  }

  private function _output($var, &$response, $in_array) {


    if ($in_array) {
        $node = Node::create($var);
        $status = $node->save();
        if($status) {
            $response->setContent(json_encode(array('result' => $status)));
        }
        else {
            $response->setContent(json_encode(array('reason' => NODE_SAVE_FAILED, 'result' => $status)));
        }
    }
    else {
          $response->setContent(json_encode(array('reason'=> CONTENT_TYPE_NOT_FOUND,  'result' => false)));
    }

    if (isset($_GET['json'])) {
      return true;
    }
    else {
      dpm($response->getContent());
      return false;
    }

  }

  public function content() {
    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');

    $content_types = array();
    foreach ($this->entityManager()->getStorage('node_type')->loadMultiple() as $type) {
      $content_types[] = $type->get('type'); 
    } 

    if (in_array($_GET['content_type'], $content_types)) {
        $fields = $this->contentTypeFields($_GET['content_type']); // Replace your content type machine name here.  

        // Create node object with attached file.
        $var = [
          'type'        => $_GET['content_type'],
          'title'       => "[".$_GET['content_type']."] ".date('Y-m-d H:i:s'),
        ];

        foreach ($fields as $field_id => $v) {
          // $var[$field_id] = 0;
        } 

        foreach ($_GET as $param_key => $param_value) {
          $var[$param_key] = $param_value;
        }

        $json_response = $this->_output($var, $response, IN_ARRAY);

    }
    else {
        $json_response = $this->_output(false, $response, NOT_IN_ARRAY);
    }

    if ($json_response) {
      return $response;
    }
    else {
      return array(
          '#markup' => '' . t('CMMCReflection there!') . '',
      );
    }

    // if ($json_response) {
    //   return $json_response;
    // }
    // else {
    // }
  }
}
