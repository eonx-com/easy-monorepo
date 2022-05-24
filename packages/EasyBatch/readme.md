<div align="center">
    <h1>EonX - EasyAsync</h1>
    <p>Makes dealing with async processes easier.</p>
</div>

---

## Documentation

Coming soon...

## TODO
- [x] Implement messenger handlers for internal messages
- [x] Implement BatchObjectManager::approve
- [x] Handle nested batch completion
- [ ] Clean up code

## Logic
### Batch dispatch
1. Create batch and its batchItems
2. Dispatch batch
   1. Persist batch and its batchItems (OPTIONAL)
   2. Iterate through eligible batchItems and dispatch them
   3. Update all batchItems from each page status to pending
   4. Return batch

### Process BatchItem
1. Identify if current message is for a batchItem, if not pass on
2. Lock rest of process against the batchItem ID
3. Fetch current batchItem based on its ID
   1. Fix batchItem status if still as created
4. Fetch current batch based on its ID
5. Inject them in current message if needed
6. Process batchItem logic
   1. Prevent process if batchItem: alreadyProcessed, can't be retried, its batch is cancelled
   2. Increase batchItem attempts
   3. Set batchItem startedAt
   4. --- If any exception up to this point, message can be retried ---
   5. Execute message logic
   6. --- If any exception after this point, batchItem must be updated separately ---
   7. Set batchItem status
   8. Set batchItem finishedAt
   9. Persist batchItem changes
   10. Update and persist batch for batchItem
   11. Dispatch batchItem events
   12. Dispatch batch events
   13. Return result of message logic

!!! MOVE CORE LOGIC FROM LISTENERS TO CORE CODE !!!
