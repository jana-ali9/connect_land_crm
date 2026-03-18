<!-- Modal لإضافة التكلفة -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addExpenseModalLabel">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="expenseForm" method="POST" action="{{ route('unit-expenses.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="allocation_type" name="allocation_type">
                    <input type="hidden" id="target_id" name="target_id">

                    <label for="expense_name" class="form-label">Expense Name</label>
                    <input type="text" id="expense_name" name="expense_name" class="form-control"
                        placeholder="Enter expense name" required>

                    <label for="amount" class="form-label mt-3">Amount</label>
                    <input type="number" id="amount" name="amount" class="form-control" min="0"
                        step="any" required>

                    <label for="category_id" class="form-label mt-3">Category</label>
                    <div class="d-flex align-items-center gap-2">
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                        </select>
                        <button type="button" id="showCategoryInputBtn" class="btn btn-success btn-sm fw-bold fs-5"
                            title="Add new category" style="font-size:20px !important;  min-width: 40px; height: 40px; padding: 0;">
                            +
                        </button>
                    </div>

                    <div id="newCategoryContainer" class="mt-2" style="display: none;">
                        <input type="text" id="new_category_name" class="form-control"
                            placeholder="New category name" />
                        <button type="button" id="addCategoryBtn" class="btn btn-primary btn-sm mt-2">Add</button>
                    </div>

                    <label for="description" class="form-label mt-3">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Set hidden inputs when clicking Add Expense buttons
        document.querySelectorAll(".add-expense-btn").forEach(button => {
            button.addEventListener("click", function() {
                let targetId = this.getAttribute("data-id");
                let allocationType = this.getAttribute("data-type");

                document.getElementById("target_id").value = targetId;
                document.getElementById("allocation_type").value = allocationType;
            });
        });

        // Load expense categories into dropdown on modal show
        const addExpenseModal = document.getElementById('addExpenseModal');
        const categorySelect = document.getElementById('category_id');

        if (addExpenseModal) {
            addExpenseModal.addEventListener('show.bs.modal', function() {
                if (categorySelect.options.length > 1) return; // Already loaded

                fetch("{{ route('expense-categories.fetch') }}")
                    .then(res => res.json())
                    .then(categories => {
                        categories.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.id;
                            option.textContent = cat.category_name;
                            categorySelect.appendChild(option);
                        });
                    })
                    .catch(err => {
                        console.error("Failed to load categories:", err);
                    });
            });
        }

        // Show new category input when + button clicked
        const showCategoryInputBtn = document.getElementById('showCategoryInputBtn');
        const newCategoryContainer = document.getElementById('newCategoryContainer');
        const addCategoryBtn = document.getElementById('addCategoryBtn');
        const newCategoryNameInput = document.getElementById('new_category_name');

        showCategoryInputBtn.addEventListener('click', () => {
            newCategoryContainer.style.display = 'block';
            showCategoryInputBtn.style.display = 'none';
            newCategoryNameInput.focus();
        });

        // Add new category via AJAX
        addCategoryBtn.addEventListener('click', () => {
            const name = newCategoryNameInput.value.trim();
            if (!name) {
                alert('Please enter a category name');
                return;
            }

            fetch("{{ route('expense-categories.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        category_name: name
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Add new category to dropdown and select it
                        const option = document.createElement('option');
                        option.value = data.category.id;
                        option.textContent = data.category.category_name;
                        option.selected = true;
                        categorySelect.appendChild(option);

                        // Reset input and hide
                        newCategoryNameInput.value = '';
                        newCategoryContainer.style.display = 'none';
                        showCategoryInputBtn.style.display = 'inline-block';
                    } else {
                        alert('Failed to add category: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => {
                    alert('Error adding category');
                    console.error(err);
                });
        });
    });
</script>
