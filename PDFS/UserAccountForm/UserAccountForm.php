<!-- Request for System User Account Form Modal -->
<div class="modal fade" id="userAccountRequestModal" tabindex="-1" aria-labelledby="userAccountRequestLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" action="PDFS/UserAccountForm/userAccountRequestPDF.php" target="_blank">
        <div class="modal-header">
          <h5 class="modal-title" id="userAccountRequestLabel">Request for System User Account Form</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <!-- Checkboxes -->
          <div class="mb-3">
            <label class="form-label">Please check the requested service:</label><br>
            <input type="checkbox" name="services[]" value="Account Creation"> Account Creation &nbsp;&nbsp;
            <input type="checkbox" name="services[]" value="Account Modification"> Account Modification &nbsp;&nbsp;
            <input type="checkbox" name="services[]" value="Account Deletion"> Account Deletion
          </div>

          <!-- Reason -->
          <div class="mb-3">
            <label class="form-label">Reason for Request:</label>
            <textarea name="reason" class="form-control"></textarea>
          </div>

          <!-- Application/System -->
          <div class="mb-3">
            <label class="form-label">Name of the Application or System:</label>
            <input type="text" name="application" class="form-control">
          </div>

          <h6>Requested User’s Information</h6>

          <!-- Individual Employee -->
          <div class="mb-3">
            <input type="checkbox" name="request_type" value="individual"> For Individual Employee Requests
          </div>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Full Name</th>
                  <th>ID No.</th>
                  <th>Username</th>
                  <th>Position/Designation</th>
                  <th>Employment Status</th>
                  <th>Access Details</th>
                </tr>
              </thead>
              <tbody id="individualRows">
                <tr>
                  <td><input type="text" name="individual[0][name]" class="form-control"></td>
                  <td><input type="text" name="individual[0][id]" class="form-control"></td>
                  <td><input type="text" name="individual[0][username]" class="form-control"></td>
                  <td><input type="text" name="individual[0][position]" class="form-control"></td>
                  <td><input type="text" name="individual[0][status]" class="form-control"></td>
                  <td><input type="text" name="individual[0][access]" class="form-control"></td>
                </tr>
              </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addIndividualRow()">+ Add Row</button>
          </div>

          <!-- Department -->
          <div class="mb-3 mt-3">
            <input type="checkbox" name="request_type" value="department"> For Office/Department Requests
          </div>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Name of Office/Department</th>
                  <th>Username</th>
                  <th>Access Details</th>
                </tr>
              </thead>
              <tbody id="departmentRows">
                <tr>
                  <td><input type="text" name="department[0][office]" class="form-control"></td>
                  <td><input type="text" name="department[0][username]" class="form-control"></td>
                  <td><input type="text" name="department[0][access]" class="form-control"></td>
                </tr>
              </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addDepartmentRow()">+ Add Row</button>
          </div>

          <!-- Permissions -->
          <div class="mb-3">
            <label class="form-label">Additional permissions or needs (if any):</label>
            <textarea name="permissions" class="form-control"></textarea>
          </div>

          <!-- Requested By -->
          <div class="mb-3">
            <label class="form-label">Requested by:</label>
            <input type="text" name="requested_by" class="form-control mb-2" placeholder="Name of Requesting Official/Personnel">
            <input type="text" name="requested_designation" class="form-control mb-2" placeholder="Designation">
          </div>

          <!-- Reviewed By -->
          <div class="mb-3">
            <label class="form-label">Reviewed and Approved by:</label>
            <input type="text" name="reviewed_by" class="form-control mb-2" placeholder="Name">
            <input type="text" name="reviewed_designation" class="form-control mb-2" placeholder="Designation">
          </div>

          <!-- Remarks -->
          <div class="mb-3">
            <label class="form-label">Remarks:</label>
            <textarea name="remarks" class="form-control"></textarea>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Generate PDF</button>
          <button type="button" class="btn btn-warning sendRequestBtn" data-form="User Account Request">
            <i class="fas fa-paper-plane"></i> Send Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let individualIndex = 1;
function addIndividualRow() {
  const container = document.getElementById('individualRows');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td><input type="text" name="individual[${individualIndex}][name]" class="form-control"></td>
    <td><input type="text" name="individual[${individualIndex}][id]" class="form-control"></td>
    <td><input type="text" name="individual[${individualIndex}][username]" class="form-control"></td>
    <td><input type="text" name="individual[${individualIndex}][position]" class="form-control"></td>
    <td><input type="text" name="individual[${individualIndex}][status]" class="form-control"></td>
    <td><input type="text" name="individual[${individualIndex}][access]" class="form-control"></td>
  `;
  container.appendChild(row);
  individualIndex++;
}

let departmentIndex = 1;
function addDepartmentRow() {
  const container = document.getElementById('departmentRows');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td><input type="text" name="department[${departmentIndex}][office]" class="form-control"></td>
    <td><input type="text" name="department[${departmentIndex}][username]" class="form-control"></td>
    <td><input type="text" name="department[${departmentIndex}][access]" class="form-control"></td>
  `;
  container.appendChild(row);
  departmentIndex++;
}
</script>
