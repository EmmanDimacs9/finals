<!-- Preventive Maintenance Plan Modal -->

<div class="modal fade" id="preventiveModal" tabindex="-1" aria-labelledby="preventiveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" action="PDFS/PreventiveMaintenancePlan/preventivePDF.php" target="_blank">
        <div class="modal-header">
          <h5 class="modal-title" id="preventiveModalLabel">Preventive Maintenance Plan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div id="items">
            <div class="item-row mb-3 border p-2 rounded">
              <label class="form-label">Item Description</label>
              <input type="text" name="items[0][description]" class="form-control mb-2" required>

              <label class="form-label">Schedule</label>
              <select name="items[0][schedule]" class="form-select">
                <option value="M">Monthly</option>
                <option value="Q">Quarterly</option>
                <option value="SA">Semi-Annually</option>
              </select>
            </div>
          </div>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addRow()">+ Add Another Item</button>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Generate PDF</button>
          <button type="button" class="btn btn-warning sendRequestBtn" data-form="Preventive Maintenance Plan">
            <i class="fas fa-paper-plane"></i> Send Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let index = 1;
function addRow() {
  const container = document.getElementById('items');
  const row = document.createElement('div');
  row.classList.add('item-row','mb-3','border','p-2','rounded');
  row.innerHTML = `
    <label class="form-label">Item Description</label>
    <input type="text" name="items[${index}][description]" class="form-control mb-2" required>

    <label class="form-label">Schedule</label>
    <select name="items[${index}][schedule]" class="form-select">
      <option value="M">Monthly</option>
      <option value="Q">Quarterly</option>
      <option value="SA">Semi-Annually</option>
    </select>
  `;
  container.appendChild(row);
  index++;
}
</script>