# TODO - Make All Files Dynamic

## Phase 1: Client-side standardization
- [ ] Refactor `backend/traveloop.js` to add small utilities (query param parsing, auth redirect helper, escapeHtml if needed)
- [ ] Remove hardcoded `BASE_URL` usage from pages (switch to `TravelLoopAPI`)

## Phase 2: Replace static mock UI with dynamic rendering
- [ ] `Itinerary.html`: remove hardcoded Bali/Tokyo/activities/timeline; render from `trip_id` or selected trip; wire `saveItinerary()` to use correct `trip_id`
- [ ] `Public-Itinerary-View.html`: replace mock hero/stats/timeline/creator with data loaded from shared `token`
- [ ] `Packing.html`: replace hardcoded checklist + localStorage-only counts with dynamic data (API-driven or add missing backend packing endpoint)
- [ ] `Admin-Dashboard.html`: remove simulated actions; render table from API response fields
- [ ] `Search.html`: ensure results render safely and remove/limit static placeholder cards/sections

## Phase 3: Remaining screens audit
- [ ] Read remaining HTML screens (Budget-Cost, Trip-Notes, Activity-Search, Create-Trip if needed) and convert any remaining static placeholders

## Phase 4: Testing
- [ ] Manual test: open each HTML file and verify no mock content remains and data loads correctly
- [ ] Fix any missing backend endpoints as needed (PHP) and re-run rendering

