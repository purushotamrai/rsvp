<?php

namespace Drupal\rsvp_core\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\rsvp_core\RsvpManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class UserRsvpController.
 */
class UserRsvpController extends ControllerBase {

  /**
   * Drupal\rsvp_core\RsvpManagerInterface definition.
   *
   * @var \Drupal\rsvp_core\RsvpManagerInterface
   */
  protected $rsvpCoreManager;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Constructs a new UserRsvpController object.
   */
  public function __construct(RsvpManagerInterface $rsvp_core_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->rsvpCoreManager = $rsvp_core_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('rsvp_core.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Add user RSVP.
   *
   * @return string
   *   Return Hello string.
   */
  public function addUserRsvp(NodeInterface $node) {
		$added = $this->rsvpCoreManager->addUserRsvpEvent($node, FALSE);

		// Prepare response.
		$response = new AjaxResponse();

		if ($added) {
			// Update attendees list.
			$attendees = $this->rsvpCoreManager->getEventRsvps($node);
			$attendees = (!empty($attendees['name'])) ? join(' | ', $attendees['name']) : '';
			$content = '<div class="field__item rsvp-field-attendees-value">' . $attendees . '</div>';
			$response->addCommand(new HtmlCommand('.rsvp-field-attendees-value', $content));
			$response->addCommand(new RemoveCommand('.rsvp-field-join-' . $node->id()));
		}

    return $response;
  }

	/**
	 * Check if current user is allowed to RSVP.
	 *
	 * @param AccountInterface $account
	 *   User account.
	 * @param $node
	 *   Event node.
	 *
	 * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
	 *   AccessResult allowed or not.
	 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	 */
  public function checkAccess(AccountInterface $account, $node) {
  	$access = FALSE;

  	// If node is of event type.
		if ($node) {
			$event = $this->entityTypeManager->getStorage('node')->load($node);
			if ($event instanceof NodeInterface && $event->getType() === 'event') {
				$access = $this->rsvpCoreManager->userRsvpAllowed($event);

				if ($access) {
					$user_rsvp_exists = $this->rsvpCoreManager->checkUserRsvpEvent($event);
					$access = !empty($user_rsvp_exists) ? FALSE : TRUE;
				}
			}
		}

		return !empty($access) ? AccessResult::allowed() : AccessResult::forbidden();
	}

}
