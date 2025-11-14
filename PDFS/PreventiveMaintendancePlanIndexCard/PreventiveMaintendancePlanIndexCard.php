<!-- Preventive Maintenance Plan Modal -->
<div class="modal fade" id="PreventiveMaintendancePlanIndexCard" tabindex="-1" aria-labelledby="preventiveModalLabel1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" action="PDFS/PreventiveMaintendancePlanIndexCard/preventivePDFindexcard.php" target="_blank">
        <div class="modal-header">
          <h5 class="modal-title" id="preventiveModalLabel1">Preventive Maintenance Plan Index Card</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div id="itemss">
            <div class="item-row mb-3 border p-2 rounded">
              <label class="form-label">Date</label>
              <input type="date" name="items[0][date]" class="form-control mb-2" required>

              <label class="form-label">Repair/Maintenance Task</label>
              <input type="text" name="items[0][report]" class="form-control mb-2" required>

              <label class="form-label">Performed By:</label>
              <input type="text" name="items[0][perform]" class="form-control mb-2" required>
            </div>
          </div>

<button type="button" class="btn btn-sm btn-outline-secondary" onclick="addRowIndexCard()">+ Add Another Item</button>        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Generate PDF</button>
          <button type="button" class="btn btn-warning sendRequestBtn" data-form="Preventive Maintenance Plan Index Card">
            <i class="fas fa-paper-plane"></i> Send Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
let indexCardIndex = 1;
function addRowIndexCard() {
  const container = document.getElementById('itemss');
  const row = document.createElement('div');
  row.classList.add('item-row','mb-3','border','p-2','rounded');
  row.innerHTML = `
    <label class="form-label">Item Description</label>
    <input type="date" name="items[${indexCardIndex}][date]" class="form-control mb-2" required>

    <label class="form-label">Repair/Maintenance Task</label>
    <input type="text" name="items[${indexCardIndex}][report]" class="form-control mb-2" required>

    <label class="form-label">Performed By:</label>
    <input type="text" name="items[${indexCardIndex}][perform]" class="form-control mb-2" required>
  `;
  container.appendChild(row);
  indexCardIndex++;
}
</script>
