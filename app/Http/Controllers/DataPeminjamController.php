<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DataPeminjam;

use App\Models\Telepon;

use App\Models\JenisKelamin;

use Session;

use Storage;

use App\Models\User;

use PDF;

use App\Exports\DataPeminjamExport;

use Maatwebsite\Excel\Facades\Excel;

class DataPeminjamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');       
    }
    
    public function index(){
        $jumlah_peminjam = DataPeminjam::count();
        $data_peminjam = DataPeminjam::orderBy('id', 'asc')->paginate(5);
        $no = 0;
        return view('data_peminjam.index', compact('data_peminjam','no', 'jumlah_peminjam'));
    }

    public function create(){
        $list_jenis_kelamin = JenisKelamin::pluck('nama_jenis_kelamin', 'id_jenis_kelamin');
        return view('data_peminjam.create', compact('list_jenis_kelamin'));
    }

    public function store(Request $request){
        $this->validate($request,[
            'kode_peminjam' => 'required|string',
            'nama_peminjam' => 'required|string|max:30',
            'tanggal_lahir' => 'required|date'
        ]);

        $this->validate($request,[
            'foto' => 'required|image|mimes:jpeg,jpg,png',
        ]);
        $foto_peminjam = $request->foto;
        $nama_file = time().'.'.$foto_peminjam->getClientOriginalExtension();
        $foto_peminjam->move('foto_peminjam/', $nama_file);

        $data_peminjam = new DataPeminjam;
        $data_peminjam->kode_peminjam = $request->kode_peminjam;
        $data_peminjam->nama_peminjam = $request->nama_peminjam;
        $data_peminjam->id_jenis_kelamin = $request->id_jenis_kelamin;
        $data_peminjam->tanggal_lahir = $request->tanggal_lahir;
        $data_peminjam->alamat = $request->alamat;
        $data_peminjam->pekerjaan = $request->pekerjaan;
        $data_peminjam->foto = $nama_file;
        $data_peminjam->user_id = $request->user_id;
        $data_peminjam->save();

        $telepon = new Telepon;
        $telepon->nomor_telepon = $request->telepon;
        $data_peminjam->telepon()->save($telepon);

        Session::flash('flash_message', 'Data peminjam berhasil disimpan');

        return redirect('data_peminjam');
    }

    public function edit($id){
        $peminjam = DataPeminjam::find($id);
        if(!empty($peminjam->telepon->nomor_telepon)){
            $peminjam->nomor_telepon = $peminjam->telepon->nomor_telepon;
        }
        $list_jenis_kelamin = JenisKelamin::pluck('nama_jenis_kelamin', 'id_jenis_kelamin');
        return view('data_peminjam.edit', compact('peminjam', 'list_jenis_kelamin'));
    }

    public function update(Request $request, $id){
        $data_peminjam = DataPeminjam::find($id);
        if($request->has('foto')){
            $foto_peminjam = $request->foto;
            $nama_file = time().'.'.$foto_peminjam->getClientOriginalExtension();
            $foto_peminjam->move('foto_peminjam/', $nama_file);
            $data_peminjam->kode_peminjam = $request->kode_peminjam;
            $data_peminjam->nama_peminjam = $request->nama_peminjam;
            $data_peminjam->id_jenis_kelamin = $request->id_jenis_kelamin;
            $data_peminjam->tanggal_lahir = $request->tanggal_lahir;
            $data_peminjam->alamat = $request->alamat;
            $data_peminjam->pekerjaan = $request->pekerjaan;
            $data_peminjam->foto = $nama_file;
            $data_peminjam->update();
            //Ketika Kolom name tabel nama_peminjmam diedit maka kolom user juga ikut berubah
            $cari_user_id = DataPeminjam::where('id', $id)->pluck('user_id');
            $user = User::where('id', $cari_user_id);
            $user->update([
                'name' => $request->nama_peminjam,
            ]);
        }
        else {
            $data_peminjam->kode_peminjam = $request->kode_peminjam;
            $data_peminjam->nama_peminjam = $request->nama_peminjam;
            $data_peminjam->id_jenis_kelamin = $request->id_jenis_kelamin;
            $data_peminjam->tanggal_lahir = $request->tanggal_lahir;
            $data_peminjam->alamat = $request->alamat;
            $data_peminjam->pekerjaan = $request->pekerjaan;
            $data_peminjam->update();
            //Ketika kolom nae pada tabel name_peminjam diedit
            //maka kolom user juga ikut berubah
            $cari_user_id = DataPeminjam::where('id', $id)->pluck('user_id');
            $user = User::where('id', $cari_user_id);
            $user->update([
                'name' => $request->nama_peminjam,
            ]);
        }
       

        if($data_peminjam->telepon){
            if($request->filled('nomor_telepon')){
                $telepon = $data_peminjam->telepon;
                $telepon->nomor_telepon = $request->input('nomor_telepon');
                $data_peminjam->telepon()->save($telepon);
            }
            else{
                $data_peminjam->telepon()->delete();
            }
        }

        else{
            if($request->filled('nomor_telepon')){
                $telepon = new Telepon;
                $telepon->nomor_telepon = $request->nomor_telepon;
                $data_peminjam->telepon()->save($telepon);
            }
        }

        Session::flash('flash_message', 'Data Peminjam Berhasil diupdate');

        return redirect('data_peminjam');
    }

    public function search(Request $request){
        $batas = 5;
        $cari = $request->kata;
        $data_peminjam = DataPeminjam::where('nama_peminjam', 'like', '%'.$cari.'%')->paginate($batas);
        $no = $batas * ($data_peminjam->currentPage() - 1);
        return view('data_peminjam.search', compact('data_peminjam', 'no', 'cari'));
    }

    public function destroy($id){
        //menghapus data user apabila data peminjma dihapus
        $cari_user_id = DataPeminjam::where('id', $id)->pluck('user_id');
        $user = User::where('id', $cari_user_id);
        $user->delete();
        //menghapus data pada tabel data_peminjam
        $data_peminjam = DataPeminjam::find($id);
        $data_peminjam->delete();
        Session::flash('flash_message', 'Data Peminjam Berhasil dihapus');
        Session::flash('penting', true);
        return redirect('data_peminjam');
    }

    public function data_peminjam_pdf()
    {
        $data_peminjam = DataPeminjam::all();
        $pdf = PDF::loadView('data_peminjam/data_peminjam_pdf', ['data_peminjam' => $data_peminjam]);
        return $pdf->download('laporan.pdf');
    }

    public function export_excel()
    {
        return Excel::download(new DataPeminjamExport, 'data_peminjam.xlsx');
    }

    public function CobaCollection(){
        $daftar = ['Budi Pranoto',
                    'Amy Delia',
                    'Cakra Lukman',
                    'Dewi Nova',
                    'Kartini Indah'
            ];
        $collection = collect($daftar)->map(function($nama){
            return ucwords($nama);           
        });
        return $collection;
    }

    public function collection_first(){
        $collection  = DataPeminjam::all()->first();
        return $collection;
    }

    public function collection_last(){
        $collection  = DataPeminjam::all()->last();
        return $collection;
    }

    public function collection_count(){
        $collection  = DataPeminjam::all();
        $jumlah = $collection->count();
        return 'Jumlah Peminjam : '.$jumlah;
    }

    public function collection_take(){
        $collection  = DataPeminjam::all()->take(3);
        return $collection;
    }

    public function collection_pluck(){
        $collection  = DataPeminjam::all()->pluck('nama_peminjam');
        return $collection;
    }

    public function collection_where(){
        $collection  = DataPeminjam::all()->where('kode_peminjam','P0004');
        return $collection;
    }

    public function collection_wherein(){
        $collection  = DataPeminjam::all()->whereIn('kode_peminjam',['P0001', 'P0003']);
        return $collection;
    }

    public function collection_toarray(){
        $collection  = DataPeminjam::select('kode_peminjam', 'nama_peminjam')->take(3)->get();
        $koleksi = $collection->toArray();
        foreach($koleksi as $peminjam){
            echo $peminjam['kode_peminjam'].'-'.$peminjam['nama_peminjam'].'<br>';
        }
    }

    public function collection_tojson(){
        $data = [
            ['kode_peminjam' => 'P0001', 'nama_peminjam' => 'Karina'],
            ['kode_peminjam' => 'P0002', 'nama_peminjam' => 'Joko'],
            ['kode_peminjam' => 'P0003', 'nama_peminjam' => 'Zainal'],
            ['kode_peminjam' => 'P0004', 'nama_peminjam' => 'Basuki Wiryawan'],
        ];
        $collection  = collect($data)->tojson();
        return $collection;
    }
}
