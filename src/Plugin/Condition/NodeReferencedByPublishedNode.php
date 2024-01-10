<?php

namespace Drupal\custom_conditions\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\islandora\Plugin\Condition\NodeReferencedByNode;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Condition for a node referenced by a published node using the configured field.
 *
 * @Condition(
 *   id = "node_referenced_by_published_node",
 *   label = @Translation("Node is referenced by at least one published node"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", required = TRUE , label = @Translation("node"))
 *   }
 * )
 */
class NodeReferencedByPublishedNode extends NodeReferencedByNode implements ContainerFactoryPluginInterface {

  /**
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    if (!$node) {
      return FALSE;
    }
    return $this->evaluateEntity($node);
  }

  /**
   * Evaluates if an entity is referenced in the configured node field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to evaluate.
   *
   * @return bool
   *   TRUE if entity is referenced..
   */
  protected function evaluateEntity(EntityInterface $entity) {
    $reference_field = $this->configuration['reference_field'];
    $config = FieldStorageConfig::loadByName('node', $reference_field);
    if ($config) {
      
      $id_count = \Drupal::entityQuery('node')
        ->accessCheck(TRUE)
        ->condition($reference_field, $entity->id())
        ->condition('status', 1, '=')
        ->count()
        ->execute();
      return ($id_count > 0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (!empty($this->configuration['negate'])) {
      return $this->t("The node is not referenced in a published node's field `@field`.", ['@field' => $this->configuration['reference_field']]);
    }
    else {
      return $this->t("The node is referenced in a published node's field `@field`.", ['@field' => $this->configuration['reference_field']]);
    }
  }

}
