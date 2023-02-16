---eonx_docs---
title: Error Codes Analyzing
weight: 1008
---eonx_docs---

# Error Codes Analyzing
Command easy-error-handler:error-codes:analyze is used to analyze error codes. It can fetch error codes from interface and enums. See section below for details.

## Sources
You can store error codes in an interface or in backed enums in the project.
If the configuration option errorCodesInterface is set, error codes will be fetched from this interface. Otherwise, all project directories are scanned to find enums that have the `#[AsErrorCodes]` attribute. Error codes from all found enums will be merged.

### Error codes from interface

`ErrorCodesFromInterfaceProvider` expects error codes in `SCREAMING_SNAKE_CASE` (aka `CONSTANT_CASE`) naming convention.

Example:
```
interface ErrorCodesInterface {

    public const ERROR_SOME_ERROR_ONE = 100;

    public const ERROR_SOME_ERROR_TWO = 101;

    public const ERROR_SOME_OTHER_ERROR = 200;
}
```

Example output:
```
+---------------------------+------------------------+
| Error code group          | Next error code to use |
+---------------------------+------------------------+
| ERROR_SOME_ERROR_*        | 102                    |
| ERROR_SOME_OTHER_ERROR_*  | 201                    |
+---------------------------+------------------------+

The error code for the new group is 300.

```

### Error codes from enums

`ErrorCodesFromEnumsProvider` expects error codes in `PascalCase` naming convention.

Example:
```
#[AsErrorCodes]
enum ErrorCodesEnum: int {

    case ErrorSomeErrorOne = 100;

    case ErrorSomeErrorTwo = 101;

    case ErrorSomeOtherError = 200;
}
```
Example output:
```
+------------------------+------------------------+
| Error code group       | Next error code to use |
+------------------------+------------------------+
| ErrorSomeError*        | 102                    |
| ErrorSomeOtherError*   | 201                    |
+------------------------+------------------------+

The error code for the new group is 300.
```
