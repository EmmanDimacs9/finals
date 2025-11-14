<!-- Request for Posting of Announcements / Greetings Modal -->
<div class="modal fade" id="postingRequestModal" tabindex="-1" aria-labelledby="postingRequestLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" action="PDFS/PostingRequestForm/postingRequestPDF.php" target="_blank">
        <div class="modal-header">
          <h5 class="modal-title" id="postingRequestLabel">Request for Posting of Announcements / Greetings</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <!-- College / Office -->
          <div class="mb-3">
            <label class="form-label">College / Office:</label>
            <input type="text" name="college_office" class="form-control">
          </div>

          <!-- Purpose -->
          <div class="mb-3">
            <label class="form-label">Purpose:</label>
            <textarea name="purpose" class="form-control"></textarea>
          </div>

          <!-- Means of Posting -->
          <div class="mb-3">
            <label class="form-label">Means of Posting:</label><br>
            <input type="checkbox" name="means[]" value="Bulletin Board"> Bulletin Board <br>
            <input type="checkbox" name="means[]" value="View Board"> View Board <br>
            <input type="checkbox" name="means[]" value="LED Board"> LED Board <br>
            <input type="checkbox" name="means[]" value="Social Media"> Social Media <br>
            <label class="form-label mt-2">Indicate Specific Location / Media Site:</label>
            <input type="text" name="location" class="form-control mb-2">
            <textarea name="media_notes" class="form-control" placeholder="Additional notes"></textarea>
          </div>

          <!-- Content Layout -->
          <div class="mb-3">
            <label class="form-label">Brief Content and Layout:</label>
            <textarea name="content" class="form-control" rows="5"></textarea>
          </div>

          <!-- Posting Period -->
          <div class="mb-3">
            <label class="form-label">Posting Period (Maximum 30 days):</label>
            <input type="text" name="period" class="form-control">
          </div>

          <!-- Requested By -->
          <div class="mb-3">
            <label class="form-label">Requested by (Head of Office/Unit):</label>
            <input type="text" name="requested_by" class="form-control mb-2" placeholder="Name">
            <input type="text" name="requested_designation" class="form-control mb-2" placeholder="Designation">
          </div>

          <!-- Recommended Approval -->
          <div class="mb-3" style="display:none">
            <label class="form-label">Recommending Approval (ICT Services):</label>
            <input type="text" name="recommended_by" class="form-control mb-2" value="Engr. JONNAH R. MELO">
            <input type="text" name="recommended_designation" class="form-control mb-2" value="Head, ICT Services">
          </div>

          <!-- Approved By -->
          <div class="mb-3" style="display:none">
            <label class="form-label">Approved by (Chancellor):</label>
            <input type="text" name="approved_by" class="form-control mb-2" value="Atty. ALVIN R. DE SILVA">
            <input type="text" name="approved_designation" class="form-control mb-2" value="Chancellor">
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
          <button type="button" class="btn btn-warning sendRequestBtn" data-form="Posting Request">
            <i class="fas fa-paper-plane"></i> Send Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
