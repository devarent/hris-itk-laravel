-- ============================================================================
-- Skema PostgreSQL (Neon Serverless) + Row-Level Security (pola crbnos/carbon)
-- Untuk eksperimen Opsi B: Migrasi & Isolasi Data Absensi HRIS ITK Laravel
-- ============================================================================

CREATE EXTENSION IF NOT EXISTS "pgcrypto";
-- Opsional jika ingin mengaktifkan pencarian semantik / RAG dengan pgvector:
-- CREATE EXTENSION IF NOT EXISTS "vector";

CREATE TABLE IF NOT EXISTS absensi (
    id BIGSERIAL PRIMARY KEY,
    karyawan_id BIGINT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME NULL,
    jam_pulang TIME NULL,
    status VARCHAR(20) NOT NULL CHECK (status IN ('hadir', 'terlambat', 'izin', 'sakit', 'cuti', 'dinas_luar', 'alfa')),
    keterangan TEXT NULL,
    lampiran VARCHAR(255) NULL,
    verified_by BIGINT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_absensi_karyawan_tanggal UNIQUE (karyawan_id, tanggal)
);

CREATE INDEX IF NOT EXISTS idx_absensi_tanggal ON absensi (tanggal);
CREATE INDEX IF NOT EXISTS idx_absensi_status ON absensi (status);

-- Aktifkan Row-Level Security (mengadopsi pola tenant/role isolation crbnos/carbon)
ALTER TABLE absensi ENABLE ROW LEVEL SECURITY;

-- Kebijakan: Admin & Atasan dapat melihat semua data; Karyawan hanya melihat datanya sendiri.
-- Aplikasi Laravel menyetel session variable sebelum query:
--   SET LOCAL app.user_role = 'karyawan';
--   SET LOCAL app.karyawan_id = '12';
DROP POLICY IF EXISTS absensi_role_isolation ON absensi;
CREATE POLICY absensi_role_isolation ON absensi
    USING (
        current_setting('app.user_role', true) IN ('admin', 'atasan')
        OR karyawan_id = NULLIF(current_setting('app.karyawan_id', true), '')::BIGINT
    )
    WITH CHECK (
        current_setting('app.user_role', true) IN ('admin', 'atasan')
        OR karyawan_id = NULLIF(current_setting('app.karyawan_id', true), '')::BIGINT
    );
