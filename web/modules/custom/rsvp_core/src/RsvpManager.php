<?php

namespace Drupal\rsvp_core;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Class RsvpManager.
 */
class RsvpManager implements RsvpManagerInterface {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

	/**
	 * Drupal\Core\Cache\CacheBackendInterface definition.
	 *
	 * @var \Drupal\Core\Cache\CacheBackendInterface
	 */
  protected $cacheManager;

	/**
	 * Drupal\Core\Cache\CacheTagsInvalidatorInterface definition.
	 *
	 * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
	 */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a new RsvpManager object.
   */
  public function __construct(Connection $database,
															AccountProxyInterface $current_user,
															ConfigFactoryInterface $config_factory,
															EntityTypeManagerInterface $entity_type_manager,
															CacheBackendInterface $cacheManager,
                              CacheTagsInvalidatorInterface $cacheTagsInvalidator) {
    $this->database = $database;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory->get('rsvp_core.config');
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheManager = $cacheManager;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
  }

	/**
	 * Calculates the great-circle distance between two points, with
	 * the Haversine formula.
	 *
	 * @param float $latitudeFrom
	 *   Latitude of start point in [deg decimal].
	 * @param float $longitudeFrom
	 *   Longitude of start point in [deg decimal].
	 * @param float $latitudeTo
	 *   Latitude of target point in [deg decimal].
	 * @param float $longitudeTo
	 *   Longitude of target point in [deg decimal].
	 * @param integer $earthRadius
	 *   Mean earth radius in [miles].
	 *
	 * @return float
	 *   Distance between points in [miles]
	 */
	protected function calculateDistance($latitudeFrom,
                                       $longitudeFrom,
                                       $latitudeTo,
                                       $longitudeTo,
                                       $earthRadius = self::RSVP_EARTH_RADIUS) {
		// Convert from degrees to radians
		$latFrom = deg2rad($latitudeFrom);
		$lonFrom = deg2rad($longitudeFrom);
		$latTo = deg2rad($latitudeTo);
		$lonTo = deg2rad($longitudeTo);

		$latDelta = $latTo - $latFrom;
		$lonDelta = $lonTo - $lonFrom;

		$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
				cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
		return $angle * $earthRadius;
	}

	/**
	 * {@inheritDoc}
	 */
	public function userRsvpAllowed(NodeInterface $event) {
		$allowed = NULL;

		// Is allowed if user is authenticated and within the radius.
		if ($this->currentUser->id()) {
			$event_geofield = $event->get('field_event_geofield')->getValue();
			$current_user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
			$user_geofield = $current_user->get('field_user_geofield')->getValue();

			if (!empty($user_geofield) && !empty($event_geofield)) {
				$user_geofield = reset($user_geofield);
				$event_geofield = reset($event_geofield);
				$distance = $this->calculateDistance(
				  $event_geofield['lat'],
          $event_geofield['lon'],
          $user_geofield['lat'],
          $user_geofield['lon']
        );
				$radius = $this->configFactory->get('rsvp_core_allowed_radius');

				if ($distance <= $radius) {
					$allowed = TRUE;
				}
				else {
					$allowed = FALSE;
				}
			}
		}

    return $allowed;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addUserRsvpEvent(NodeInterface $event, $checkRsvpExists = TRUE) {
		$added = NULL;

		if ($this->currentUser->id()) {
			$exists = FALSE;
			if ($checkRsvpExists) {
				$exists = $this->checkUserRsvpEvent($event);
			}

			if (empty($exists)) {
				try {
					$this->database->insert('rsvp_event_data')
						->fields([
							'uid' => $this->currentUser->id(),
							'nid' => $event->id(),
						])
						->execute();
					$added = TRUE;
				}
				catch (\Exception $exception) {
					watchdog_exception('rsvp_core', $exception);
				}
			}
		}

		// Invalidate self::RSVP_EVENT_DATA_CACHE_CID cache.
		if ($added) {
			$this->cacheManager->invalidate(self::RSVP_EVENT_DATA_CACHE_CID);
			$this->cacheTagsInvalidator->invalidateTags(['rsvp_event_data:' . $event->id()]);
		}

		return $added;
	}

	/**
	 * {@inheritDoc}
	 */
	public function checkUserRsvpEvent(NodeInterface $event) {
		$attendee = FALSE;

		if ($this->currentUser->id()) {
			try {
				$query = $this->database->select('rsvp_event_data', 're')
					->fields('re', ['uid', 'nid'])
					->condition('uid', $this->currentUser->id())
					->condition('nid', $event->id());

				$result = $query->execute();

				$attendee = $result->fetchAllKeyed(0, 1);
			}
			catch (\Exception $exception) {
				watchdog_exception('rsvp_core', $exception);
			}
		}

		return !empty($attendee) ? $attendee : FALSE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEventRsvps(NodeInterface $event) {
		$attendees = [];
		$event_id = $event->id();

		if (($cached_data = $this->cacheManager->get(self::RSVP_EVENT_DATA_CACHE_CID))
			&& !isset($cached_data->data[$event_id])) {
			$attendees = $cached_data->data;
		}
		else {
			try {
				$attendees[$event_id] = [];
				$query = $this->database->select('rsvp_event_data', 're');
				$query->join('users_field_data', 'ufd', 'ufd.uid=re.uid');
				$query
					->fields('re', ['uid', 'nid'])
				  ->fields('ufd', ['name'])
					->condition('ufd.status', '1');

				$result = $query->execute();
				$result = $result->fetchAll();

				foreach ($result as $item) {
				  // Considering username for display.
					$attendees[$item->nid]['name'][] = $item->name;
					$attendees[$item->nid]['uid'][] = $item->uid;
				}

				// Cache the rsvp_event_data result.
				$this->cacheManager->set(self::RSVP_EVENT_DATA_CACHE_CID, $attendees, Cache::PERMANENT, ['rsvp_event_data']);
			}
			catch (\Exception $exception) {
				watchdog_exception('rsvp_core', $exception);
			}
		}

		return !empty($attendees[$event_id]) ? $attendees[$event_id] : [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRsvpLink($event_id) {
		$link = [];
		$event = $this->entityTypeManager->getStorage('node')->load($event_id);

		if (!empty($event) && empty($this->checkUserRsvpEvent($event))) {
			if (!empty($this->userRsvpAllowed($event))) {
				try {
					$link = Link::createFromRoute(
						$this->configFactory->get('rsvp_core_join_text'),
						'rsvp_core.user_rsvp',
						['node' => $event_id],
						[
							'attributes' => [
								'class' => ['btn use-ajax rsvp-field-join-' . $event_id],
							],
						]
					);
					$link = $link->toRenderable();
					$link['#attached']['library'][] = 'core/drupal.ajax';
				}
				catch (\Exception $exception) {
					watchdog_exception('rsvp_core', $exception);
				}
			}
			elseif ($this->currentUser->id()) {
				$link = [
					'#type' => 'html_tag',
					'#tag' => 'button',
					'#value' => $this->configFactory->get('rsvp_core_cannot_join_text'),
					'#attributes' => [
						'class' => ['disabled', 'btn'],
						'disabled' => TRUE,
					]
				];
			}
		}

		return $link;
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteEventRsvps(EntityInterface $event) {
		$this->database->delete('rsvp_event_data')
			->condition('nid', $event->id())
			->execute();
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteUserRsvps(UserInterface $user) {
		$this->database->delete('rsvp_event_data')
			->condition('uid', $user->id())
			->execute();
	}

}
