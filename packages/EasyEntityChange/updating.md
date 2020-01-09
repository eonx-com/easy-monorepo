UPGRADING
---

0.11.x -> 2.0.x

- BC BREAK: The EntityChangeEvent's signature has changed and now contains an array of ChangedEntity DTO objects instead of arrays of classes/ids
- BC BREAK: The DeleteData event no longer exists, and to achieve the same functionality you will need to implement and inject a `DeleteEntityEnrichmentInterface` service that provides metadata for a deletion event.
