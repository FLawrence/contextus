#Project plan for editing the Shakespeare Data

# Introduction #

The form needs to load the data from the triple store for a given entity or event, allow the user to edit the data and save it back to the triple store as their interpretation of the entity/event


# Details #

## Milestone 1: Basic Entity Editor {COMPLETED} ##

  * Editor gets character and location information from the automatically generated in the data store and presents this to the user.
  * User can edit the character name/location name strings
  * User can save this information back to the data store as their version of the character/location

Notes: Have to work out about identification/authorisation. Can anyone write? Use names? Hashed email addresses? Can link in with site log in?

Progress So Far (Keith): Implemented login with OpenID (& Google's flavour), user able to edit character names. Data put back into fourstore in a graph identified by a SHA1 hash of the returned OpenID. Still a few wrinkles to work out with fourstore-php.

COMPLETED - Entity and Location editors now work and save to graph specified by the open log in ID

BONUS - Event and Entity information display also working


## Milestone 2: Enhanced Entity Editor ##

  * User can assert that two entities are the same
  * User can assert basic geographic links between locations (next to, within etc)?

Progress so far (Keith): Implemented functions to add triples from properties and dropdown/text options

(Faith): Duplicated code implemented on characteredit page on locationedit page and added way to differentiate between which groups of properties are displayed as options.

To Do:
  1. ~~Implement addition of reciprocal values~~
  1. ~~Multiple dropdowns (in javascript add create and add properties to multiple dropdown boxes - still creating options on a per-page basis but can have multiple options within that page)~~
  1. ~~Improve display~~

Types of dropdowns needed
  1. ~~content of relevant types of instances from Dream graph~~
  1. content from csv file
  1. content from relevant types of instance from meta gragh

useful queries
```

SELECT DISTINCT ?l
WHERE {
  { <http://www.contextus.net/resource/meta/Female> ?p ?o; rdfs:label ?l }
  UNION
  { ?s <http://purl.org/ontomedia/ext/common/trait#identified-with> <http://www.contextus.net/resource/meta/Female>; rdfs:label ?l }
}
ORDER BY ?l

```

## Milestone 3: Basic Event Editor ##

  * Editor can load up the information related to an event:
    * Event Type (drop down of allowed event types)
    * Location (drop down of known locations)
    * Subject Entity (checky boxes of known characters - if more than one is selected then a group should be made/used)
    * Subjects Involved (checky boxes of known characters - if more then one is selected then a group should be made/used. This list should include any subject entities even if they are not checked)
    * References (Checky boxes of all known characters and locations)
    * Event specific information
      * In the case of travel events then:
        * From (drop down of known locations)
        * To (drop down of known locations)
  * User can edit the event details
  * User can save this information back to the data store as their version of the character/location

## Milestone 4: Extended Entity Editor ##

  * User can create new character and location entities

## Milestone 5: Extended Event Editor ##

  * User can create and populate new events
  * User can select from more types of events

## Milestone 6: Bonus Options ##

  * User can delete events
  * User can delete character and location entities

## Milestone 7: Social Stuff ##

  * User profile page (save foaf data for annotator)

## Milestone 8: Bloody Statistics ##

  * Page showing fun and interesting statistics drawn from the graph
    * Number of editors
    * Most corrected
    * Most contentious
    * etc