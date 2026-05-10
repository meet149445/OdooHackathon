// Frontend helper for Packing.html
// (Intentionally small and self-contained.)

function getTripIdFromQuery() {
  const sp = new URLSearchParams(window.location.search);
  const tripId = sp.get('trip_id');
  return tripId ? parseInt(tripId, 10) : null;
}

