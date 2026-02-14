    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-dark text-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="bi bi-book me-2"></i>Sistem Perpustakaan Digital</h6>
                    <p class="text-muted mb-0"></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar me-1"></i>
                        &copy; <?= date('Y') ?> Perpustakaan Digital. All rights reserved.
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-code-slash me-1"></i>

                    </p>
                </div>
            </div>
        </div>
    </footer>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="/LibraryManagement/assets/js/script.js"></script>


    <!-- Custom JavaScript untuk halaman spesifik -->
    <?php if (isset($customJS)): ?>
        <script>
            <?= $customJS ?>
        </script>
    <?php endif; ?>

    </body>

    </html>