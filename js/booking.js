/* ============================================================
   RestauRant – booking.js
   ============================================================ */

'use strict';

const BASE_BK = window.APP_BASE || '';

// ============================================================
//  Date Picker – enforce min date = today
// ============================================================
const dateInput = document.getElementById('bookingDate');
if (dateInput) {
  const today = new Date().toISOString().split('T')[0];
  dateInput.min = today;
  dateInput.value = today;

  dateInput.addEventListener('change', () => {
    loadBookedSlots(dateInput.value);
  });

  // Load slots for today on init
  loadBookedSlots(today);
}

// ============================================================
//  Load booked slots from API and disable those buttons
// ============================================================
async function loadBookedSlots(date) {
  const data = await fetchJSON(`${BASE_BK}/includes/api/get_booked_slots.php?date=${encodeURIComponent(date)}`);
  const booked = (data.success ? data.booked_slots : []) || [];

  document.querySelectorAll('.time-slot-btn').forEach(btn => {
    const slot = btn.dataset.slot;
    const isBooked = booked.includes(slot);
    btn.disabled = isBooked;
    btn.title = isBooked ? 'This slot is already booked' : '';
    if (isBooked) btn.classList.remove('selected');
  });
}

// ============================================================
//  Time Slot Selection
// ============================================================
let selectedSlot = '';
document.querySelectorAll('.time-slot-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    if (btn.disabled) return;
    document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedSlot = btn.dataset.slot;
    const hiddenInput = document.getElementById('selectedTimeSlot');
    if (hiddenInput) hiddenInput.value = selectedSlot;
  });
});

// ============================================================
//  Guest Count +/- Buttons
// ============================================================
let guestCount = 2;
const guestDisplay = document.getElementById('guestCountDisplay');
const guestInput   = document.getElementById('guestCountInput');

function updateGuestDisplay() {
  if (guestDisplay) guestDisplay.textContent = guestCount;
  if (guestInput)   guestInput.value = guestCount;
}

const guestPlus  = document.getElementById('guestPlus');
const guestMinus = document.getElementById('guestMinus');
if (guestPlus) {
  guestPlus.addEventListener('click', () => {
    if (guestCount < 20) { guestCount++; updateGuestDisplay(); }
  });
}
if (guestMinus) {
  guestMinus.addEventListener('click', () => {
    if (guestCount > 1) { guestCount--; updateGuestDisplay(); }
  });
}

// ============================================================
//  Booking Form Submit
// ============================================================
const bookingForm = document.getElementById('bookingForm');
if (bookingForm) {
  bookingForm.addEventListener('submit', async e => {
    e.preventDefault();

    if (!selectedSlot) {
      showToast('Please select a time slot.', 'warning');
      return;
    }

    const date  = document.getElementById('bookingDate')?.value;
    const notes = document.getElementById('bookingNotes')?.value || '';

    if (!date) {
      showToast('Please select a date.', 'warning');
      return;
    }

    const submitBtn = bookingForm.querySelector('[type="submit"]');
    showSpinner(submitBtn, 'Booking…');

    const fd = new FormData();
    fd.append('booking_date', date);
    fd.append('time_slot',    selectedSlot);
    fd.append('guests',       guestCount);
    fd.append('notes',        notes);

    const data = await fetchJSON(`${BASE_BK}/includes/api/booking_create.php`, {
      method: 'POST', body: fd,
    });

    hideSpinner(submitBtn);

    if (data.success) {
      showToast(data.message || 'Booking confirmed!', 'success');
      // Reset form
      bookingForm.reset();
      selectedSlot = '';
      guestCount = 2;
      updateGuestDisplay();
      document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('selected'));
      const today = new Date().toISOString().split('T')[0];
      if (dateInput) { dateInput.value = today; loadBookedSlots(today); }

      // Show success state
      const successEl = document.getElementById('bookingSuccess');
      if (successEl) successEl.style.display = 'block';
    } else {
      showToast(data.message || 'Booking failed. Please try again.', 'error');
    }
  });
}
