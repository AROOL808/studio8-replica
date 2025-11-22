document.addEventListener('DOMContentLoaded', function () {
  // package selection highlight
  const packageCards = document.querySelectorAll('.package-card');
  packageCards.forEach(card => {
    card.addEventListener('click', function (e) {
      // simulate radio click when clicking the whole label
      const radio = this.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
      packageCards.forEach(c => c.classList.remove('selected'));
      this.classList.add('selected');
    });
  });

  // handle giftcard toggle
  const useGift = document.getElementById('use-gift');
  const personalData = document.getElementById('personal-data');
  const schedule = document.getElementById('schedule');
  const fullName = document.getElementById('fullName');
  const email = document.getElementById('email');
  const phone = document.getElementById('phone');
  const dateInput = document.getElementById('date');
  const timeInput = document.getElementById('time');

  function toggleGiftcardFields() {
    const isGift = useGift.checked;
    if (isGift) {
      // hide personal data & schedule; remove required
      personalData.classList.add('hidden');
      schedule.classList.add('hidden');
      fullName.required = false;
      email.required = false;
      phone.required = false;
      dateInput.required = false;
      timeInput.required = false;
    } else {
      personalData.classList.remove('hidden');
      schedule.classList.remove('hidden');
      fullName.required = true;
      email.required = true;
      phone.required = true;
      dateInput.required = true;
      timeInput.required = true;
    }
  }

  useGift.addEventListener('change', toggleGiftcardFields);
  toggleGiftcardFields(); // initial state

  // form submission: basic validation and collect data
  const bookingForm = document.getElementById('booking-form');
  bookingForm.addEventListener('submit', function (e) {
    // ensure a package selected
    const selectedPackage = document.querySelector('input[name="package"]:checked');
    if (!selectedPackage) {
      e.preventDefault();
      alert('Pilih paket terlebih dahulu.');
      return;
    }

    // HTML5 validation
    if (!bookingForm.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
      bookingForm.classList.add('was-validated');
      // optional: focus first invalid
      const firstInvalid = bookingForm.querySelector(':invalid');
      if (firstInvalid) firstInvalid.focus();
      return;
    }

    // prevent actual submit in demo — replace with real submit handler as needed
    e.preventDefault();
    const payload = {
      package: selectedPackage.value,
      variant: bookingForm.variant.value,
      extra: bookingForm.extra.value,
      payment: bookingForm.payment.value,
      useGift: useGift.checked,
      name: fullName.value || null,
      email: email.value || null,
      phone: phone.value || null,
      date: dateInput.value || null,
      time: timeInput.value || null,
      note: bookingForm.note.value || null
    };

    // For demo, show confirmation summary; replace with API call as needed
    alert('Booking berhasil (demo):\n' + JSON.stringify(payload, null, 2));
    // Optionally reset form:
    // bookingForm.reset();
    // packageCards.forEach(c => c.classList.remove('selected'));
    // toggleGiftcardFields();
  });
});
