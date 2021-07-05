<?php

namespace Drupal\Tests\idc_defaults\Kernel;

use Drupal\taxonomy\Entity\Term;
use Drupal\Tests\node\Kernel\NodeAccessTestBase;
use Drupal\Tests\workbench_access\Traits\WorkbenchAccessTestTrait;
use Drupal\workbench_access\WorkbenchAccessManagerInterface;

/**
 * Tests the field_member_of constraint.
 *
 * @group idc_defaults
 */
class ConstraintTest extends NodeAccessTestBase {

  use WorkbenchAccessTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'datetime',
    'user',
    'system',
    'filter',
    'field',
    'text',
    'taxonomy',
    'workbench_access',
    'idc_defaults',
    'devel',
  ];

  /**
   * Access control scheme.
   *
   * @var \Drupal\workbench_access\Entity\AccessSchemeInterface
   */
  protected $scheme;

  /**
   * User section storage.
   *
   * @var \Drupal\workbench_access\UserSectionStorage
   */
  protected $userStorage;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('workbench_access');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('section_association');
    $this->installSchema('system', 'key_value');

    $node_type = $this->createContentType(['type' => 'repository_item']);
    $this->vocabulary = $this->setUpVocabulary();
    $this->accessHandler = $this->container->get('entity_type.manager')->getAccessControlHandler('node');
    $this->state = $this->container->get('state');
    $this->setUpTaxonomyFieldForEntityType('node', $node_type->id(), $this->vocabulary->id());
    $this->scheme = $this->setUpTaxonomyScheme($node_type, $this->vocabulary);
    $this->userStorage = $this->container->get('workbench_access.user_section_storage');
    $this->createEntityReferenceField('node', 'repository_item', 'field_member_of', 'Member Of', 'node', 'default', [], 2);
  }

  /**
   * Tests the WorkbenchAccess constraint.
   */
  public function testConstraint() {
    // Permissions matching the collection level and global admins for repo
    // items.
    $permissions = [
      'create repository_item content',
      'edit any repository_item content',
      'edit own repository_item content',
      'access content',
    ];

    // Create a section.
    $term = Term::create([
      'vid' => $this->vocabulary->id(),
      'name' => 'Some collection',
    ]);
    $term->save();

    // Create two collection_admin users with the same permissions but one will
    // be part of a community.
    $collection_admin_community_user = $this->createUser($permissions);
    $this->userStorage->addUser($this->scheme, $collection_admin_community_user, [$term->id()]);

    $collection_admin_user = $this->createUser($permissions);

    // Global admin can bypass workbench.
    $permissions[] = 'bypass workbench access';
    $global_admin_user = $this->createUser($permissions);

    // Node that belongs to a community.
    $community_node = $this->drupalCreateNode([
      'type' => 'repository_item',
      WorkbenchAccessManagerInterface::FIELD_NAME => $term->id(),
    ]);

    // Switch to the collection admin community user.
    $this->setCurrentUser($collection_admin_community_user);
    $node_referencing_a_community_node = $this->drupalCreateNode([
      'type' => 'repository_item',
      'field_member_of' => $community_node->id(),
    ]);

    $violations = $node_referencing_a_community_node->validate();
    $this->assertCount(0, $violations, 'Collection admin within a community able to add to a node within a community.');

    // Switch to the collection admin user.
    $this->setCurrentUser($collection_admin_user);
    $node_referencing_a_community_node_no_access = $this->drupalCreateNode([
      'type' => 'repository_item',
      'field_member_of' => $community_node->id(),
    ]);
    $violations = $node_referencing_a_community_node_no_access->validate();
    $this->assertCount(1, $violations, 'Collection admin not part of a community unable to add to a node.');
    $this->assertEquals('The user does not have access to ingest into this object.', $violations->get(0)->getMessage(), 'Incorrect constraint validation message found');

    // Switch to the global admin user.
    $this->setCurrentUser($global_admin_user);
    $node_referencing_a_community_global_admin = $this->drupalCreateNode([
      'type' => 'repository_item',
      'field_member_of' => $community_node->id(),
    ]);
    $violations = $node_referencing_a_community_global_admin->validate();
    $this->assertCount(0, $violations, 'Global admin can add to whatever collection they want.');

  }

}
