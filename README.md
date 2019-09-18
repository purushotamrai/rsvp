# RSVP EVENT - Drupal Project

Event Listing and RSVP functionality - Drupal 8 Project 

## Architecture

### Site Architecture

Project is based on `drupal-composer/drupal-project` composer project template and installs Drupal Core 8.7. 

#### Event - Content Type and User Entity

Event Content type is the content container which is used for events content
management. Following fields are available to collect event information:
* field_event_date: To store event date.
* field_event_description: To store event descriptive information.
* field_event_image: To store event featured image.
* field_event_location: To store event location in human readable format.
* field_event_geofield: Corresponding geocode to support lat long of the 
  event location.

User Entity uses following two fields to store and use user location:
* field_user_location: User location in Human readable Format.
* field_user_geofield: Stores corresponding geocode in lat-long format. 

#### Major Contrib Modules Used

* [Address](https://drupal.org/project/address): To store event and user location.
* [Geocode](https://drupal.org/project/geocode): To support conversion of string location to lat-long geocode.
* [Geofield](https://drupal.org/project/geofield): To save geocode to field and use funcationalities and APIs provided by geocode modules.
* [Leaflet](https://drupal.org/project/leaflet): To display map.

#### RSVP Custom Modules

##### RSVP Core
 
**Contains customizations related to RSVP Event Project**

* Custom Table 'rsvp_event_data' with 2 columns nid and uid to store RSVP data.
* RSVP and Read More pseudo fields are available to handle display of RSVP links, attendees list and Read More Button.
* RsvpManager service provides methods to handle RSVP related validations and database transactions.
  * Checks User RSVP is allowed or not.
  * Adds user RSVP to the event.
  * Gets Event RSVPs.
  * Gets RSVP Link based on current user.
* **Caching Strategy** based on contexts and tags to improve performance of RSVP Links and hence Event Detail Page.
* UserRsvpController to handle ajax call to allow user to RSVP to any event and update Attendees list on the spot using Ajax Commands.

### Configuration Management

We are using features to export configurations related to RSVP project. Following features are available:

* RSVP Config Bundle
* RSVP Content Bundle
* RSVP User Bundle

## RSVP Configurations and Reports

* RSVP Configuration Form: /admin/config/rsvp_core/rsvp-config
  * RSVP Allowed Radius
  * RSVP Join Button text
  * RSVP Cannot Join Button text
  
* RSVP Report: /admin/reports/rsvp-event
  * Columns: Name, Email, Uid, Title, Nid
  * Filters: Event Title and User Name 

## Frontend (Theming) Details

### RSVP Sub Theme
Custom theme developed using classy as base theme. 

* Defines breakpoints and global styling library.
* Theme utilizes Gulp Tasks to manage and support frontend Development.
* Directories 'css' and 'js' stores final consumable minified files.
* Mobile First approach has been used for theming. 

## Assumptions

* Event featured image is being used at both detail page and teaser view.
* No provision for user to cancel rsvp.
* We are using OpenStreetMap and Leaflet APIs for location and map related functionality(s) *as Google Maps API is now premium.*
* RSVP Theme utilizes certain bootstrap framework and fontawesome stylings.
* Provided Styleguide provided instructions to use Roboto font, while it was using 'Helvetica' font. RSVP theme uses 
  styleguide for all display related decisions.
