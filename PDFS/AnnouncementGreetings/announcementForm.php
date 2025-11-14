<!-- Request For Posting Of Announcements/Greetings Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" action="PDFS/AnnouncementGreetings/announcementPDF.php" target="_blank">
        
        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title" id="announcementModalLabel">REQUEST FOR POSTING OF ANNOUNCEMENTS/GREETINGS</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
          <div class="mb-3">
            <label for="purpose" class="form-label">Purpose:</label>
            <input type="text" id="purpose" name="purpose" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Means of Posting:</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="posting[]" value="Bulletin Board" id="bulletinBoard">
              <label class="form-check-label" for="bulletinBoard">Bulletin Board</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="posting[]" value="View Board" id="viewBoard">
              <label class="form-check-label" for="viewBoard">View Board</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="posting[]" value="LED Board" id="ledBoard">
              <label class="form-check-label" for="ledBoard">LED Board</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="posting[]" value="Social Media" id="socialMedia">
              <label class="form-check-label" for="socialMedia">Social Media</label>
            </div>
          </div>

          <div class="mb-3">
            <label for="location" class="form-label">Indicate Specific Location / Media Site:</label>
            <input type="text" id="location" name="location" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="content" class="form-label">Brief Content and Layout:</label>
            <input type="text" id="content" name="content" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="postingPeriod" class="form-label">Posting Period: <small class="text-muted">(Maximum 30 days)</small></label>
            <input type="text" id="postingPeriod" name="postingPeriod" class="form-control" required>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Generate PDF</button>
          <button type="button" class="btn btn-warning sendRequestBtn" data-form="Announcement Request">
            <i class="fas fa-paper-plane"></i> Send Request
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
