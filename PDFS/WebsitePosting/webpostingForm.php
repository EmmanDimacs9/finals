<!-- Website Posting Request Modal -->

<div class="modal fade" id="webpostingModal" tabindex="-1" aria-labelledby="webpostingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" action="PDFS/WebsitePosting/webpostingPDF.php" target="_blank">
        
        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title" id="webpostingModalLabel">Website Posting Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">

          <div class="mb-3">
            <label for="office" class="form-label">Requesting Office/Unit:</label>
            <input type="text" id="office" name="office" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="datePosting" class="form-label">Proposed Date of Posting:</label>
            <input type="date" id="datePosting" name="datePosting" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="durationPosting" class="form-label">Duration of Posting:</label>
            <input type="text" id="durationPosting" name="durationPosting" class="form-control" placeholder="e.g., 1 week" required>
          </div>

          <div class="mb-3">
            <label for="purpose" class="form-label">Purpose:</label>
            <input type="text" id="purpose" name="purpose" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="content" class="form-label">Content:</label>
            <textarea id="content" name="content" class="form-control" rows="3" required></textarea>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Generate PDF</button>
          <button type="button" class="btn btn-warning sendRequestBtn" data-form="Website Posting">
            <i class="fas fa-paper-plane"></i> Send Request
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
