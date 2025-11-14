<!-- ISP Evaluation Modal -->
<div class="modal fade" id="ispEvaluationModal" tabindex="-1" aria-labelledby="ispEvaluationLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" action="PDFS/ISPEvaluation/ispEvaluationPDF.php" target="_blank">
        <div class="modal-header">
          <h5 class="modal-title" id="ispEvaluationLabel">Existing Internet Service Provider’s Evaluation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Provider Info -->
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">External Provider’s Name</label>
              <input type="text" name="provider_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">Date of Evaluation</label>
              <input type="date" name="evaluation_date" class="form-control" required>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control">
          </div>

          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Contact Person</label>
              <input type="text" name="contact_person" class="form-control">
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">Period Covered</label>
              <input type="text" name="period" class="form-control">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Position</label>
              <input type="text" name="position" class="form-control">
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">Tel. No.</label>
              <input type="text" name="tel_no" class="form-control">
            </div>
          </div>

          <!-- Criteria Rates -->
          <h6 class="mt-3">Criteria Evaluation</h6>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Criteria</th>
                  <th>Rate (Dropdown)</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1. Uptime Commitment</td>
                  <td>
                    <select name="uptime_rate" class="form-select">
                      <option value="60%">99–100% Uptime (60%)</option>
                      <option value="50%">98–98.9% Uptime (50%)</option>
                      <option value="40%">97–97.9% Uptime (40%)</option>
                      <option value="30%">96–96.9% Uptime (30%)</option>
                      <option value="20%">0–95.9% Uptime (20%)</option>
                    </select>
                  </td>
                  <td><input type="text" name="uptime_remarks" class="form-control"></td>
                </tr>
                <tr>
                  <td>2. Network Latency</td>
                  <td>
                    <select name="latency_rate" class="form-select">
                      <option value="30%">Low/Low/Low (30%)</option>
                      <option value="25%">Low/Low/High (25%)</option>
                      <option value="25%">High/High/Low (25%)</option>
                      <option value="20%">Low/High/High (20%)</option>
                      <option value="20%">High/Low/High (20%)</option>
                      <option value="20%">High/High/Low (20%)</option>
                      <option value="15%">High/High/High (15%)</option>
                    </select>
                  </td>
                  <td><input type="text" name="latency_remarks" class="form-control"></td>
                </tr>
                <tr>
                  <td>3. Technical Support</td>
                  <td>
                    <select name="support_rate" class="form-select">
                      <option value="10%">Within 48h + Updates within 2h (10%)</option>
                      <option value="9%">Within 48h + Updates >2h (9%)</option>
                      <option value="8%">>48h + Updates within 2h (8%)</option>
                      <option value="7%">>48h + Updates >2h (7%)</option>
                    </select>
                  </td>
                  <td><input type="text" name="support_remarks" class="form-control"></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Evaluator Info -->
          <div class="row mt-3">
            <div class="col-md-6 mb-2">
              <label class="form-label">Evaluated by (Name)</label>
              <input type="text" name="evaluator" class="form-control">
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">Immediate Supervisor</label>
              <input type="text" name="supervisor" class="form-control">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Generate PDF</button>
          <button type="button" class="btn btn-warning sendRequestBtn" data-form="ISP Evaluation">
            <i class="fas fa-paper-plane"></i> Send Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
