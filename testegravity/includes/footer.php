<?php
// includes/footer.php
$user = getUserSession();
?>
    <?php if ($user): ?>
        </main> <!-- /content-body -->
    </div> <!-- /main-wrapper -->
    <?php endif; ?>
</div> <!-- /app-container -->

<script src="public/js/charts.js?v=<?= time() ?>"></script>
<script src="public/js/main.js?v=<?= time() ?>"></script>
</body>
</html>
