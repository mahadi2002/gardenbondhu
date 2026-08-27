## What this does and why

<!-- The why matters more than the what — see CONTRIBUTING.md. -->

## How this was tested

<!-- Which flow did you actually click through? Which edge case did you check?
     `php tests/smoke.php` is necessary but not sufficient for anything UI-facing. -->

## Checklist

- [ ] `php -l` passes on every changed file (CI checks this too, but faster to know now)
- [ ] `php tests/smoke.php` passes, and I added a check for any new logic in
      `Crypto`, `Csrf`, `Validator`, `Operators`, `Totp`, a Repository's
      teaser/full column split, or `SubscriptionService`
- [ ] No new Composer dependency
- [ ] Every new DB call is a prepared statement; every new echoed variable goes through `e()`
- [ ] If this touches plant/problem/guide content: written in conversational Bangla (আপনি-form), not translated-sounding — and I did not touch a `toxic_to_pets` value without a citable source
- [ ] If this is a UI change: checked it in both light and dark theme
