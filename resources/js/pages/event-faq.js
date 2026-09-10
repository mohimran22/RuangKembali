document.addEventListener('DOMContentLoaded', function () {

    const container = document.getElementById('faqContainer');
    const addButton = document.getElementById('addFaqButton');

    if (!container || !addButton) {
        return;
    }


    let faqIndex = getNextIndex();

    function getNextIndex() {

        const cards = container.querySelectorAll(
            '.faq-create-card'
        );

        let maxIndex = -1;

        cards.forEach(card => {

            const index = parseInt(
                card.dataset.faqIndex,
                10
            );

            if (!isNaN(index) && index > maxIndex) {
                maxIndex = index;
            }

        });

        return maxIndex + 1;
    }

    function removeEmptyState() {

        const emptyState =
            document.getElementById('faqEmptyState');

        if (emptyState) {
            emptyState.remove();
        }
    }

    function showEmptyState() {

        const cards = container.querySelectorAll(
            '.faq-create-card'
        );

        if (cards.length > 0) {
            return;
        }

        if (document.getElementById('faqEmptyState')) {
            return;
        }

        const emptyState = document.createElement('div');

        emptyState.id = 'faqEmptyState';
        emptyState.className = 'event-gallery-empty';

        emptyState.innerHTML = `
            <i class="ti ti-help-circle"></i>

            <div class="fw-semibold mt-2">
                Belum ada FAQ
            </div>

            <div class="text-secondary small">
                Klik "Tambah FAQ" untuk menambahkan pertanyaan dan jawaban.
            </div>
        `;

        container.appendChild(emptyState);
    }

    function createFaq(index) {

        const card =
            document.createElement('div');

        card.className =
            'faq-create-card mb-2 p-3 border rounded';

        card.dataset.faqIndex = index;


        card.innerHTML = `
            <div class="row g-2">

                <!-- Pertanyaan -->
                <div class="col-md-5">

                    <label class="form-label small mb-1">
                        Pertanyaan
                    </label>

                    <input type="text"
                           name="faqs[${index}][question]"
                           class="form-control form-control-sm"
                           placeholder="Contoh: Apakah event ini gratis?">

                </div>


                <!-- Jawaban -->
                <div class="col-md-6">

                    <label class="form-label small mb-1">
                        Jawaban
                    </label>

                    <textarea name="faqs[${index}][answer]"
                              class="form-control form-control-sm"
                              rows="2"
                              placeholder="Tuliskan jawaban FAQ"></textarea>

                </div>


                <!-- Hapus -->
                <div class="col-md-1 d-flex align-items-end">

                    <button type="button"
                            class="btn btn-sm btn-danger w-100 faq-remove-btn"
                            title="Hapus FAQ">

                        <i class="ti ti-trash"></i>

                    </button>

                </div>

            </div>
        `;


        return card;
    }

    addButton.addEventListener('click', function () {

        removeEmptyState();

        const card = createFaq(faqIndex);

        container.appendChild(card);

        faqIndex++;

    });


    /**
     * Hapus FAQ
     *
     * Event delegation supaya FAQ yang
     * dibuat menggunakan JS juga bisa dihapus.
     */
    container.addEventListener('click', function (event) {

        const removeButton =
            event.target.closest('.faq-remove-btn');

        if (!removeButton) {
            return;
        }


        const card =
            removeButton.closest('.faq-create-card');

        if (!card) {
            return;
        }


        card.remove();

        showEmptyState();

    });


    /**
     * Rapikan index sebelum submit
     */
    const form = container.closest('form');

    if (form) {

        form.addEventListener('submit', function () {

            const cards =
                container.querySelectorAll(
                    '.faq-create-card'
                );


            cards.forEach((card, newIndex) => {

                card.dataset.faqIndex = newIndex;


                const inputs =
                    card.querySelectorAll(
                        'input[name^="faqs["], textarea[name^="faqs["]'
                    );


                inputs.forEach(input => {

                    input.name = input.name.replace(
                        /faqs\[\d+\]/,
                        `faqs[${newIndex}]`
                    );

                });


                // Update sort_order
                const sortInput =
                    card.querySelector('.faq-sort-order');

                if (sortInput) {
                    sortInput.value = newIndex;
                }

            });

        });

    }

});