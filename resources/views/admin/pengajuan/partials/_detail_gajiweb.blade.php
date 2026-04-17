<template x-if="dataDetail.kategori_layanan === 'GajiWeb' && dataDetail.detail_gaji">
    <div class="grid grid-cols-2 gap-4 mt-3 pt-4 border-t border-gray-200/60 text-xs">
        <div>
            <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Bulan Periode</p>
            <p class="font-black text-blue-700 uppercase text-sm" x-text="dataDetail.detail_gaji.bulan_periode"></p>
        </div>
        <div class="text-right">
            <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Kategori ADK</p>
            <p class="font-bold text-gray-700" x-text="dataDetail.detail_gaji.kategori_adk"></p>
        </div>
        <div class="col-span-1">
            <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Jenis Pegawai</p>
            <p class="font-bold text-gray-700 uppercase" x-text="dataDetail.detail_gaji.jenis_pegawai"></p>
        </div>
        <div class="col-span-1 text-right">
            <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Jenis Proses</p>
            <p class="font-bold text-gray-700 uppercase" x-text="dataDetail.detail_gaji.jenis_proses"></p>
        </div>
    </div>
</template>