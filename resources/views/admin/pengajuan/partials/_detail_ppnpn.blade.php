<template x-if="dataDetail.kategori_layanan === 'PPNPN' && dataDetail.detail_ppnpn">
    <div class="grid grid-cols-2 gap-4 mt-3 pt-4 border-t border-gray-200/60 text-xs">
        <div>
            <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Bulan Periode</p>
            <p class="font-black text-blue-700 uppercase text-sm" x-text="dataDetail.detail_ppnpn.bulan_periode"></p>
        </div>
        <div class="text-right">
            <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Kode Anak Satker</p>
            <p class="font-bold text-gray-700" x-text="dataDetail.detail_ppnpn.kode_anak_satker"></p>
        </div>
        <div class="col-span-1">
            <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Jenis ADK</p>
            <p class="font-bold text-gray-700 uppercase" x-text="dataDetail.detail_ppnpn.jenis_adk"></p>
        </div>
        <div class="col-span-1 text-right">
            <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">ID ADK</p>
            <p class="font-bold text-gray-700 uppercase" x-text="dataDetail.detail_ppnpn.id_adk"></p>
        </div>
        <div class="col-span-2">
            <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Tanggal Antrean</p>
            <p class="font-bold text-gray-700 uppercase" x-text="dataDetail.detail_ppnpn.tanggal_antrean"></p>
        </div>
    </div>
</template>