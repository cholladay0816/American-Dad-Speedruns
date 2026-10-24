# CI 40040141002 follow-up

The failed run tested f72d208e6a7b48475d40b6c7a69bbe4fc647067a. The follow-up
branch starts from merge 808141da62439b14330b0639e9004333acf61104, whose application
tree matches that tested commit.

## Evidence and proposed changes

- Main browser stage: 171 passed, 24 failed (22 visual comparisons and two
  functional assertions). Database-session stage: 19 passed, one failed.
  Ranking/accessibility stage: 20 passed. PHP dependency audit passed.
- The WebKit reset trace contains one successful POST /reset-password (302),
  followed by a second GET of the reset form but **no second POST**. After the
  second password fill, the DOM snapshot shows the password string prepended to
  the email and the password field still empty. The next confirmation fill
  succeeds. Native form validation therefore prevents the intended request.
  This is evidence of an initial autofocus/input race, not evidence that the
  application accepted a reused token. The test now observes initial email
  autofocus, verifies all form values, and waits for the actual POST response
  on both submissions. The invalid-token assertion remains mandatory.
- Mobile Safari's deletion modal became visible after a successful Livewire
  update but its password field did not gain focus, in both session modes.
  The current implementation uses a 250 ms timeout from a server-dispatched
  event, independently of the dialog visibility lifecycle. The proposed fix
  watches dialog visibility and focuses an explicitly marked input after
  Alpine's DOM update and a rendering frame. Other dialogs do not opt in.
  The test retains its initial focus/Tab/Escape assertions and now also checks
  focus on reopening. The precise timing failure and efficacy of this fix
  cannot be proven from the trace alone because it does not record the timer
  callback itself. The replacement passed the local five-project suite in
  both session modes, including initial focus, Tab, Escape and reopening.
- All 22 original screenshot failures and goldens remain intact. Direct review
  of the Chromium profile pair confirms that its large height reduction includes
  removal of retired billing and inactive 2FA panels, plus populated profile
  inputs and darker navigation. This is a substantive intentional difference,
  not a tiny paint fluctuation. It does not automatically approve the other
  comparisons or authorize replacing the reference images.

## Validation and remaining gate

Node syntax checking of gaps.spec.ts and git diff --check passed. During the
user-approved preview pause on October 24, local validation passed:

- File sessions: 20 browser tests passed, zero failed/skipped (269.88 seconds).
- Database sessions: 20 browser tests passed, zero failed/skipped (213.77 seconds).
- Each run also passed 34 backend tests / 187 assertions.
- Both browser runs cover Chromium, Firefox, WebKit, mobile Chrome and mobile
  Safari: reset/token reuse/new-password login, deletion with password rejection
  and keyboard focus, session behavior, keyboard login and a missing-page case.
- Exactly one container at a time, 3 GiB memory/swap ceiling, 2 CPUs, one worker,
  network disabled for tests, synthetic fixtures, and an 85% host memory guard.
  Sampled host memory peaks: 74.4% file sessions; 77.4% database sessions.

Reproduction inside the prepared PHP 8.5 / Playwright image:

```sh
ADSR_SESSION_DRIVER=file ADSR_EVIDENCE=ci-auth-file sh tests/upgrade/run-single.sh gaps.spec.ts
ADSR_SESSION_DRIVER=database ADSR_EVIDENCE=ci-auth-database sh tests/upgrade/run-single.sh gaps.spec.ts
```

Raw local reports, screenshots, videos and traces are under
`tests/baseline/ci-auth-file{,-report}` and
`tests/baseline/ci-auth-database{,-report}` (ignored by Git). Backend logs and
JUnit reports are preserved as `artifacts/ci-auth-{file,database}-phpunit.*`.

The full browser matrix was not repeated in this focused follow-up. All 22
original visual comparisons remain unresolved; goldens are unchanged. Remote
CI still requires a reviewed publication and rerun. The preview uses its original
checkout, not these uncommitted follow-up edits.

The owner subsequently authorized publishing this follow-up and removing the
local preview. Its port forwarding, container and guard were removed without
changing other services. No production changes were made. Downloaded raw trace
evidence stays outside the published source and must not be committed.
