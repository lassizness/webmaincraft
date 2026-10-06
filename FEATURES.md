# Feature map from the supplied reference

The supplied older game portal was used as a product/structure reference, not as a codebase to copy wholesale.

| Reference idea | HiveTech implementation |
|---|---|
| Advanced Yii2 multi-tier project | frontend / backend / console / common |
| Registration / auth / password reset | HiveTech ID using shared Gravit users |
| LK | Account module |
| News / categories | News + NewsCategory |
| Site pages / widgets | Pages + homepage slides |
| Shop | Categories + products + purchases |
| Donate / gold | Wallet + payment orders + ledger |
| Gift / promo | Promo codes + delivery queue |
| Support | Tickets + replies + attachments + email |
| SMTP settings | Environment-based SymfonyMailer (secrets outside DB) |
| Recaptcha | Optional Cloudflare Turnstile |
| Admin users / logs | RBAC user management + audit log |
| Game delivery | Minecraft RCON queue |
| Game profile visuals | Gravit skin/cloak endpoints |
| Future forum | DiscourseConnect SSO via users.uuid |

Game-specific Perfect World concepts (arena, factions, PW item database, MMOTOP-specific jobs) are intentionally not copied into the Minecraft portal.
