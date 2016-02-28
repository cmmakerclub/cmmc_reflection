<?php
/**
 * @file
 * Contains \Drupal\hello\HelloController.
 */

namespace Drupal\hello; 

use Drupal\Core\Controller\ControllerBase; 

#use Drupal\node\Entity\Node;
use \Drupal\node\Entity\Node;
use Drupal\field\FieldConfigInterface;

class HelloController extends ControllerBase { 
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
  public function content() {
    $content_types = array();
    foreach ($this->entityManager()->getStorage('node_type')->loadMultiple() as $type) {
      $content_types[] = $type->get('type'); 
    } 
   
    if (in_array($_GET['content_type'], $content_types)) {
        $fields = $this->contentTypeFields('dht11'); // Replace your content type machine name here.  
        foreach ($fields as $field_id => $v) {
          #dpm($v);
        } 

        // Create node object with attached file.
        $node = Node::create([
          'type'        => 'dht11',
          'title'       => new Date(),
          #'field_temperature' => [
          #  'target_id' => $file->id(), 
          #],
        ]);
        $node->field_humidity = $_GET['humidity'];
        $node->field_temperature = $_GET['temperature'];
        dpm($node->save());

    }
    else {
	dpm("NOT FOUND");
    }


    return array(
        '#markup' => '' . t('Hello there!') . '',
    );
  }
}
