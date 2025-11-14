  <!-- ICT Service Request Form Modal -->
  <div class="modal fade" id="ictServiceRequestModal" tabindex="-1" aria-labelledby="ictServiceRequestLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form method="POST" action="PDFS/ICTRequestForm/ictServiceRequestPDF.php" target="_blank">
          <div class="modal-header">
            <h5 class="modal-title" id="ictServiceRequestLabel">ICT Service Request Form</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="row">
              <!-- Campus -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Campus</label>
                <input type="text" name="campus" class="form-control" required>
              </div>
              <!-- ICT SRF No -->
              <div class="col-md-6 mb-3">
                <label class="form-label">ICT SRF No.</label>
                <input type="text" name="ict_srf_no" class="form-control" required>
              </div>
            </div>

            <div class="row">
              <!-- Client’s Name -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Client’s Name</label>
                <input type="text" name="client_name" class="form-control" required>
              </div>
              <!-- Technician assigned -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Technician Assigned</label>
                <input type="text" name="technician" class="form-control" required>
              </div>
            </div>

            <div class="row">
              <!-- Date/Time of Call -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Date/Time of Call</label>
                <input type="datetime-local" name="date_time_call" class="form-control" required>
              </div>
              <!-- Required Response Time -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Required Response Time</label>
                <input type="text" name="response_time" class="form-control" required>
              </div>
            </div>

            <!-- Service Requirements -->
            <div class="mb-3">
              <label class="form-label">Service Requirements</label>
              <textarea name="requirements" class="form-control" rows="3" required></textarea>
            </div>

            <!-- Accomplishment -->
            <div class="mb-3">
              <label class="form-label">Accomplishment</label>
              <textarea name="accomplishment" class="form-control" rows="3"></textarea>
            </div>

            <!-- Remarks -->
            <div class="mb-3">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="3"></textarea>
            </div>

            <!-- Evaluation -->
            <h6 class="mt-3">Evaluation (1 = Very Dissatisfied, 5 = Very Satisfied)</h6>
            <p>Select your satisfaction level for each statement:</p>
            <div class="table-responsive">
              <table class="table table-bordered text-center align-middle">
                <thead>
                  <tr>
                    <th>Evaluation Statements</th>
                    <th>5<br><small>Very Satisfied</small></th>
                    <th>4<br><small>Satisfied</small></th>
                    <th>3<br><small>Neutral</small></th>
                    <th>2<br><small>Dissatisfied</small></th>
                    <th>1<br><small>Very Dissatisfied</small></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Response time to your initial call for service</td>
                    <td><input type="radio" name="eval_response" value="5" required></td>
                    <td><input type="radio" name="eval_response" value="4"></td>
                    <td><input type="radio" name="eval_response" value="3"></td>
                    <td><input type="radio" name="eval_response" value="2"></td>
                    <td><input type="radio" name="eval_response" value="1"></td>
                  </tr>
                  <tr>
                    <td>Quality of service provided to resolve the problem</td>
                    <td><input type="radio" name="eval_quality" value="5" required></td>
                    <td><input type="radio" name="eval_quality" value="4"></td>
                    <td><input type="radio" name="eval_quality" value="3"></td>
                    <td><input type="radio" name="eval_quality" value="2"></td>
                    <td><input type="radio" name="eval_quality" value="1"></td>
                  </tr>
                  <tr>
                    <td>Courtesy and professionalism of the attending ICT staff</td>
                    <td><input type="radio" name="eval_courtesy" value="5" required></td>
                    <td><input type="radio" name="eval_courtesy" value="4"></td>
                    <td><input type="radio" name="eval_courtesy" value="3"></td>
                    <td><input type="radio" name="eval_courtesy" value="2"></td>
                    <td><input type="radio" name="eval_courtesy" value="1"></td>
                  </tr>
                  <tr>
                    <td>Overall satisfaction with the assistance/service provided</td>
                    <td><input type="radio" name="eval_overall" value="5" required></td>
                    <td><input type="radio" name="eval_overall" value="4"></td>
                    <td><input type="radio" name="eval_overall" value="3"></td>
                    <td><input type="radio" name="eval_overall" value="2"></td>
                    <td><input type="radio" name="eval_overall" value="1"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Generate PDF</button>
            <button type="button" class="btn btn-warning sendRequestBtn" data-form="ICT Service Request Form">
              <i class="fas fa-paper-plane"></i> Send Request
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
