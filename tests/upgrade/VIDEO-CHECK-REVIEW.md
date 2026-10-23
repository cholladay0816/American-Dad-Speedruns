# Video availability policy (separate business change)

The legacy native `file_get_contents` comparison often treated HTTP errors as an existing video. This implementation follows the requested private/inaccessible versus missing/unavailable distinction, with conservative provider-health checks. It is separately reviewable from the framework migration.

The checker uses the public YouTube oEmbed endpoint without credentials, with a 3-second connection timeout and 5-second total timeout. Redirects, HTML challenges and unrecognized responses are not evidence for automatic disqualification.

| Evidence | Classification |
| --- | --- |
| HTTP 200 with a YouTube video oEmbed object | Available; establishes batch health |
| Plain-text 401 `Unauthorized` or 403 `Forbidden` | Candidate: private or inaccessible; privacy not confirmed |
| Plain-text 404 `Not Found` or 410 `Gone` | Candidate: missing or unavailable; deletion not confirmed |
| Structured API error, 429, 5xx, connection failure | Provider failure; defer the entire batch |
| Other response/body | Unknown; retain that run |

No run is changed until all candidates have been inspected. A batch needs at least one available video and no provider failure. Uniform 401/403/404/410 responses therefore leave all runs unchanged, including a single-video batch with no healthy evidence. This deliberately favors deferral over mass disqualification.

The exact accepted plain-text body, HTTP status, request URL and body SHA-256 are logged with the affected run. Its disqualification reason preserves the observed status/body and ambiguity; the evidence link points to that video's oEmbed request. Already-disqualified runs are excluded from later scans, preventing duplicate notices.

A successful peer response and a bounded oEmbed error support an accessibility classification; they do not prove whether the owner deleted the video or set a privacy option. Provider behavior has not been checked live. The [oEmbed specification](https://oembed.com/#section2.3.5) defines 401 for private resources and 404 as no representation for the URL. The [YouTube API error documentation](https://developers.google.com/youtube/data/reference/rest/v3/errors) also demonstrates why generic 403 statuses cannot be treated as private-video proof: authorization and quota errors can share that status. No Data API credentials or calls are introduced here.

Focused tests in `VideoQueueUpgradeTest` cover a mixed batch containing available, 401, 403, 404 and 410 videos; exact evidence and distinct reasons; duplicate-scan idempotence; provider-wide auth/not-found/quota/transient failures; HTML ambiguity; and a database queue worker delivering only to array mail. All fixtures are synthetic and networking is disabled.
