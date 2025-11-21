<!-- System Request Modal -->
<div class="modal fade" id="systemReqsModal" tabindex="-1" aria-labelledby="systemReqsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" action="PDFS/SystemRequest/systemReqsPDF.php" target="_blank">

        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title" id="systemReqsModalLabel">System Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">

          <!-- Requesting Office/Unit -->
          <div class="mb-3">
            <label for="office" class="form-label">Requesting Office/Unit:</label>
            <input type="text" id="office" name="office" class="form-control" required>
          </div>

          <!-- Type of Request -->
          <div class="mb-3">
            <label class="form-label">Type of Request:</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="sysType[]" value="Correction of system issue" id="correction">
              <label class="form-check-label" for="correction">Correction of system issue</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="sysType[]" value="System enhancement" id="enhancement">
              <label class="form-check-label" for="enhancement">System enhancement</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="sysType[]" value="New System" id="newSystem">
              <label class="form-check-label" for="newSystem">New System</label>
            </div>
          </div>

          <!-- Urgency -->
          <div class="mb-3">
            <label class="form-label">Urgency:</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="urgency[]" value="Immediate attention required" id="immediate">
              <label class="form-check-label" for="immediate">Immediate attention required</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="urgency[]" value="Handle in normal priority" id="normal">
              <label class="form-check-label" for="normal">Handle in normal priority</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="urgency[]" value="Defer until new system is developed" id="defer">
              <label class="form-check-label" for="defer">Defer until new system is developed</label>
            </div>
          </div>

          <!-- Name of System -->
          <div class="mb-3">
            <label for="nameSystem" class="form-label">Name of the Existing / Proposed System:</label>
            <input type="text" id="nameSystem" name="nameSystem" class="form-control" required>
          </div>

          <!-- Description of Request -->
          <div class="mb-3">
            <label for="descRequest" class="form-label">Description of Request:</label>
            <small class="form-text text-muted">(Detailed functional and/or technical information. Use attachment if necessary)</small>
            <textarea id="descRequest" name="descRequest" class="form-control" rows="4" required></textarea>
          </div>

          <hr>

          <!-- Signature Blocks -->
          <div class="row mb-3">
            <div class="col-md-6">
              <h6>Requested By</h6>
              <label class="form-label">Name of Requesting Official / Personnel:</label>
              <input type="text" name="reqByName" class="form-control" required>
              <label class="form-label mt-2">Designation:</label>
              <input type="text" name="reqByDesignation" class="form-control" required>
              <label class="form-label mt-2">Date:</label>
              <input type="date" name="reqByDate" class="form-control" required>
            </div>
            <div class="col-md-6">
              <h6>Recommending Approval</h6>
              <label class="form-label">Name:</label>
              <input type="text" name="recApprovalName" class="form-control" value="Director, ICT Services" required>
              <label class="form-label mt-2">Date:</label>
              <input type="date" name="recApprovalDate" class="form-control" required>
            </div>
          </div>

          <h6>Approved By</h6>
          <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" name="approvedByName" class="form-control" value="Vice President for Development and External Affairs" required>
            <label class="form-label mt-2">Date:</label>
            <input type="date" name="approvedByDate" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="remarks" class="form-label">Remarks:</label>
            <textarea id="remarks" name="remarks" class="form-control" rows="2"></textarea>
          </div>

          <hr>

          <!-- ICT Services Section -->
          <h6>To be completed by the ICT Services</h6>
          <div class="mb-3">
            <label class="form-label">Date:</label>
            <input type="date" name="ictDate" class="form-control">
            <label class="form-label mt-2">Assigned to:</label>
            <input type="text" name="ictAssigned" class="form-control">
            <label class="form-label mt-2">Description of Accomplished Tasks:</label>
            <textarea name="ictTasks" class="form-control" rows="3"></textarea>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <h6>Work Done By</h6>
              <label class="form-label">Signature over Printed Name:</label>
              <input type="text" name="ictWorkByName" class="form-control">
              <label class="form-label mt-2">Designation:</label>
              <input type="text" name="ictWorkByDesignation" class="form-control">
              <label class="form-label mt-2">Date:</label>
              <input type="date" name="ictWorkByDate" class="form-control">
            </div>
            <div class="col-md-6">
              <h6>Conforme</h6>
              <label class="form-label">Signature over Printed Name:</label>
              <input type="text" name="ictConformeName" class="form-control">
              <label class="form-label mt-2">Designation:</label>
              <input type="text" name="ictConformeDesignation" class="form-control">
              <label class="form-label mt-2">Date:</label>
              <input type="date" name="ictConformeDate" class="form-control">
            </div>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Generate PDF</button>
          <button type="button" class="btn btn-warning sendRequestBtn" data-form="System Request">
            <i class="fas fa-paper-plane"></i> Send Request
          </button>
        </div>

      </form>
    </div>
  </div>
</div>