# Design patterns (EscapeZoom Core)

| Component | Pattern | Why |
|-----------|---------|-----|
| `GatewayRouter` | Front Controller | Single HTTP entry (`POST /ajax`) |
| `GetSansesJsonAction`, `BookingGatewayActions::*` | Command / Action | One use-case per handler |
| `LegacySansAdapter` | Adapter (temporary) | Bridge to `web-service` handlers when `EZ_BOOKING_NATIVE_SANSES` is off |
| `SansAvailabilityService` | Application Service | Native `get_sanses` (Eloquent, no reservation bootstrap) |
| `SansAvailabilityCalculator` | Strategy / Facade | Chooses native vs legacy by feature flag |
| `DayTypeResolver`, `DaySlotBuilder`, `SansStatusResolver`, `SansPricingResolver` | Domain services | Ported rules from `reservation-handlers.inc.php` |
| `BookingService` | Application Service | Orchestration, caching, domain API |
| `*Repository` (Eloquent / mysqli) | Repository | Persistence separated from business rules |
| `ProductData`, `BookingHistory`, `BookingLock` | Active Record (Eloquent) | Table mapping |
| `ActionRegistry` | Registry | Decouple action names from classes |
| `SignatureVerifier` | Strategy (auth) | Pluggable verification of signed requests |
| `CapsuleManager` | Singleton (infrastructure) | One Capsule per request |

## Module layout

```
ez_core/src/
  Core/           Bootstrap
  Infrastructure/ Database (CapsuleManager)
  Models/         Eloquent entities
  Modules/
    AjaxGateway/  HTTP + auth
    Booking/      Actions, Services, Infrastructure
  Support/        Global helpers (ez_table, …)
```

Theme (`escapezoom-v2`) must not own business rules; it enqueues assets and renders views only.
