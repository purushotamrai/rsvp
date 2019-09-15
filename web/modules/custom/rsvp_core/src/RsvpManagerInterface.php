<?php

namespace Drupal\rsvp_core;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Interface RsvpManagerInterface.
 */
interface RsvpManagerInterface {

	const RSVP_EARTH_RADIUS = 3959;

	const RSVP_EVENT_DATA_CACHE_CID = 'rsvp_event_data';

	/**
	 * Check if user RSVP allowed based on distance.
	 *
	 * @param NodeInterface $event
	 *   Event node.
	 *
	 * @return mixed
	 *   Allowed or not.
	 */
	public function userRsvpAllowed(NodeInterface $event);

	/**
	 * Ass user RSVP to event.
	 *
	 * @param NodeInterface $event
	 *   Node event.
	 * @param bool $checkRsvpExists
	 *   Flag to check rsvp exists.
	 *
	 * @return mixed
	 *   Added or not.
	 */
	public function addUserRsvpEvent(NodeInterface $event, $checkRsvpExists = TRUE);

	/**
	 * Check is user is attendee.
	 *
	 * @param NodeInterface $event
	 *   Event node.
	 *
	 * @return mixed
	 *   User is attendee or not.
	 */
	public function checkUserRsvpEvent(NodeInterface $event);

	/**
	 * Fetch event RSVPs.
	 *
	 * @param NodeInterface $event
	 *   Event node.
	 *
	 * @return mixed
	 *   List of users.
	 */
	public function getEventRsvps(NodeInterface $event);

	/**
	 * Get RSVP Link.
	 *
	 * @param int|string $event_id
	 *   Event id.
	 * @return mixed
	 *   RSVP Link.
	 */
	public function getRsvpLink($event_id);

	/**
	 * Delete event RSVPs.
	 *
	 * @param EntityInterface $event
	 *   Event node.
	 *
	 * @return mixed
	 */
	public function deleteEventRsvps(EntityInterface $event);

	/**
	 * Delete user RSVPs.
	 *
	 * @param UserInterface $user
	 *   User entity.
	 *
	 * @return mixed
	 */
	public function deleteUserRsvps(UserInterface $user);

}
