<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Enum;

enum HttpStatusCode: int
{
    case Accepted = 202;

    case AlreadyReported = 208;

    case BadGateway = 502;

    case BadRequest = 400;

    case Conflict = 409;

    case Continue = 100;

    case Created = 201;

    case EarlyHints = 103;

    case ExpectationFailed = 417;

    case FailedDependency = 424;

    case Forbidden = 403;

    case Found = 302;

    case GatewayTimeout = 504;

    case Gone = 410;

    case IamTeapot = 418;

    case ImUsed = 226;

    case InsufficientStorage = 507;

    case InternalServerError = 500;

    case LengthRequired = 411;

    case Locked = 423;

    case LoopDetected = 508;

    case MethodNotAllowed = 405;

    case MisdirectedRequest = 421;

    case MovedPermanently = 301;

    case MultiStatus = 207;

    case MultipleChoices = 300;

    case NetworkAuthenticationRequired = 511;

    case NoContent = 204;

    case NonAuthoritativeInformation = 203;

    case NotAcceptable = 406;

    case NotExtended = 510;

    case NotFound = 404;

    case NotImplemented = 501;

    case NotModified = 304;

    case Ok = 200;

    case PartialContent = 206;

    case PaymentRequired = 402;

    case PermanentlyRedirect = 308;

    case PreconditionFailed = 412;

    case PreconditionRequired = 428;

    case Processing = 102;

    case ProxyAuthenticationRequired = 407;

    case RequestEntityTooLarge = 413;

    case RequestHeaderFieldsTooLarge = 431;

    case RequestTimeout = 408;

    case RequestUriTooLong = 414;

    case RequestedRangeNotSatisfiable = 416;

    case Reserved = 306;

    case ResetContent = 205;

    case SeeOther = 303;

    case ServiceUnavailable = 503;

    case SwitchingProtocols = 101;

    case TemporaryRedirect = 307;

    case TooEarly = 425;

    case TooManyRequests = 429;

    case Unauthorized = 401;

    case UnavailableForLegalReasons = 451;

    case UnprocessableEntity = 422;

    case UnsupportedMediaType = 415;

    case UpgradeRequired = 426;

    case UseProxy = 305;

    case VariantAlsoNegotiatesExperimental = 506;

    case VersionNotSupported = 505;
}
