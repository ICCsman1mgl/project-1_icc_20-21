    </main>
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
    <script nonce="<?= htmlspecialchars(cspNonce(), ENT_QUOTES, 'UTF-8') ?>" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap 5 -->
    <script nonce="<?= htmlspecialchars(cspNonce(), ENT_QUOTES, 'UTF-8') ?>" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script nonce="<?= htmlspecialchars(cspNonce(), ENT_QUOTES, 'UTF-8') ?>" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script nonce="<?= htmlspecialchars(cspNonce(), ENT_QUOTES, 'UTF-8') ?>" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert -->
    <script nonce="<?= htmlspecialchars(cspNonce(), ENT_QUOTES, 'UTF-8') ?>" src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script nonce="<?= htmlspecialchars(cspNonce(), ENT_QUOTES, 'UTF-8') ?>" src="<?= defined('BASE_URL') ? BASE_URL . '/assets/js/script.js' : '/assets/js/script.js' ?>"></script>
    </body>

    </html>
