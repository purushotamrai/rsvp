<?php

/**
 * @file
 * Provide views data for rsvp_core.module.
 */

/**
 * Implements hook_views_data().
 */
function rsvp_core_views_data() {
  $data = [];

  $data['rsvp_event_data']['table']['group'] = t('RSVP Event Data');
  $data['rsvp_event_data']['table']['provider'] = 'rsvp_core';
  $data['rsvp_event_data']['table']['base'] = [
    'field' => 'nid',
    'title' => t('Event RSVP entries'),
    'help' => t('Contains a list of rsvp entries.'),
  ];

  $data['rsvp_event_data']['nid'] = [
    'title' => t('Nid'),
    'help' => t('The node ID of the event.'),
    'field' => [
      'id' => 'numeric',
    ],
    'relationship' => [
      'base' => 'node_field_data',
      'id' => 'standard',
      'label' => t('Event Node'),
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];

  $data['rsvp_event_data']['uid'] = [
    'title' => t('Uid'),
    'help' => t('The user ID of a user.'),
    'field' => [
      'id' => 'numeric',
    ],
    'relationship' => [
      'base' => 'users_field_data',
      'id' => 'standard',
      'label' => t('User Entity'),
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  return $data;
}
