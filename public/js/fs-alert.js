// public/js/fs-alert.js

const FSAlert = {
  sukses: (pesan = 'Data berhasil disimpan.') => {
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: pesan,
      confirmButtonColor: '#0D7377',
      timer: 2200,
      timerProgressBar: true,
      customClass: { popup: 'fs-swal-popup' }
    });
  },

  gagal: (pesan = 'Terjadi kesalahan, silakan coba lagi.') => {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: pesan,
      confirmButtonColor: '#D32F4E',
      customClass: { popup: 'fs-swal-popup' }
    });
  },

  konfirmasiHapus: (callback, namaItem = 'data ini') => {
    Swal.fire({
      icon: 'warning',
      title: 'Hapus Data?',
      text: `Anda yakin ingin menghapus ${namaItem}? Tindakan ini tidak dapat dibatalkan.`,
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#D32F4E',
      cancelButtonColor: '#9CA3AF',
      reverseButtons: true,
      customClass: { popup: 'fs-swal-popup' }
    }).then((result) => { if (result.isConfirmed) callback(); });
  },

  konfirmasiLogout: (callback) => {
    Swal.fire({
      icon: 'question',
      title: 'Keluar dari Sistem?',
      text: 'Sesi Anda akan diakhiri.',
      showCancelButton: true,
      confirmButtonText: 'Ya, Keluar',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#0D7377',
      cancelButtonColor: '#9CA3AF',
      reverseButtons: true,
      customClass: { popup: 'fs-swal-popup' }
    }).then((result) => { if (result.isConfirmed) callback(); });
  },

  konfirmasiPosting: (callback, pesan = 'Jurnal akan diposting permanen ke Buku Besar dan tidak dapat dihapus.') => {
    Swal.fire({
      icon: 'warning',
      title: 'Posting Transaksi?',
      text: pesan,
      showCancelButton: true,
      confirmButtonText: 'Ya, Posting',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#0D7377',
      cancelButtonColor: '#9CA3AF',
      reverseButtons: true,
      customClass: { popup: 'fs-swal-popup' }
    }).then((result) => { if (result.isConfirmed) callback(); });
  },

  konfirmasiApproval: (callback, aksi = 'menyetujui') => {
    Swal.fire({
      icon: 'question',
      title: `Yakin ingin ${aksi} transaksi ini?`,
      showCancelButton: true,
      confirmButtonText: 'Ya, Lanjutkan',
      cancelButtonText: 'Batal',
      confirmButtonColor: aksi === 'menolak' ? '#D32F4E' : '#1E8E5A',
      cancelButtonColor: '#9CA3AF',
      reverseButtons: true,
      customClass: { popup: 'fs-swal-popup' }
    }).then((result) => { if (result.isConfirmed) callback(); });
  },

  peringatanBudget: (sisaAnggaran) => {
    Swal.fire({
      icon: 'warning',
      title: 'Melebihi Plafon Anggaran!',
      html: `Transaksi ini melampaui sisa anggaran cost center sebesar <b>Rp ${sisaAnggaran}</b>. Diperlukan persetujuan tambahan.`,
      confirmButtonText: 'Mengerti',
      confirmButtonColor: '#E89A1C',
      customClass: { popup: 'fs-swal-popup' }
    });
  },

  errorUnbalance: () => {
    Swal.fire({
      icon: 'error',
      title: 'Jurnal Tidak Balance',
      text: 'Total Debit harus sama dengan total Kredit sebelum transaksi dapat disimpan.',
      confirmButtonColor: '#D32F4E',
      customClass: { popup: 'fs-swal-popup' }
    });
  }
};
