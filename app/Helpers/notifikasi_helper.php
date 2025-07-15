<?php

use App\Models\NotifikasiModel;

if (!function_exists('kirimNotifikasi')) {
    function kirimNotifikasi(string $roleSlug, string $tipe, string $isi, ?int $relasiId = null, ?int $outletId = null, ?string $pengirimRole = null)
    {
        $notifikasiModel = new NotifikasiModel();
        $db = db_connect();

        $builder = $db->table('auth_groups_users')
            ->select('auth_groups_users.user_id')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->join('users', 'users.id = auth_groups_users.user_id')
            ->where('auth_groups.name', $roleSlug);

        if ($outletId !== null) {
            $builder->where('users.outlet_id', $outletId);
        }

        $userIds = $builder->get()->getResultArray();
        $now = date('Y-m-d H:i:s');

        $relasiColumn = match ($tipe) {
            'permintaan'       => 'permintaan_id',
            'penerimaan'       => 'penerimaan_id',
            'pengiriman'       => 'pengiriman_id',
            'pengajuan'        => 'pengajuan_id',
            'bukti_transfer'   => 'bukti_transfer_id',
            'bukti_pembelian'  => 'bukti_pembelian_id',
            default            => null,
        };

        foreach ($userIds as $user) {
            $data = [
                'user_id'        => $user['user_id'], // penerima
                'tipe'           => $tipe,
                'isi'            => $isi,
                'dibaca'         => 0,
                'created_at'     => $now,
                'tujuan_role'    => $roleSlug,
                'pengirim_role'  => $pengirimRole ?? 'unknown',
                'relasi_id'      => $relasiId,
            ];

            if ($relasiColumn && $relasiId) {
                $data[$relasiColumn] = $relasiId;
            }

            $notifikasiModel->insert($data);
        }
    }
    if (!function_exists('outlet_nama')) {
        function outlet_nama($id)
        {
            $db = \Config\Database::connect();
            $outlet = $db->table('outlet')->where('id', $id)->get()->getRowArray();
            return $outlet['nama_outlet'] ?? 'Unknown Outlet';
        }
    }
}
