<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index()
    {
        // $products = Product::all();
        $products = Product::paginate(5);

        // $data = [
        //     'status' => 200,
        //     'message' => 'Data produk berhasil diambil',
        //     'data' => $products
        // ];

        return response()->json($products, Response::HTTP_OK);
    }

    public function show($id)
    {
        $product = Product::find($id);

        // jika data ditemukan by id
        if (is_null($product)) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data produk tidak ditemukan',
            ];
            return response()->json($data, Response::HTTP_NOT_FOUND);
        } else {
            $data = [
                'status' => Response::HTTP_OK,
                'message' => 'Data produk berhasil diambil',
                'data' => $product
            ];
            return response()->json($data, Response::HTTP_OK);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer',
            'description' => 'required|string',
            'image' => 'image|mimes:png,jpg,webp,jpeg,svg|max:2048'
        ], [
            'name.required' => 'Nama product harus diisi',
        ]);
        $input = $request->all();
        // dd($input);
        // logika image
        if ($image = $request->file('image')) {
            $target = 'assets/images/';
            $img_name = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($target, $img_name);
            $input['image'] = "$img_name"; // memasukkan nama
        }
        // masukan ke database dengan orm
        Product::create($input);

        $data = [
            'status' => Response::HTTP_CREATED,
            'message' => 'Data produk berhasil ditambahkan',
            'data' => $input
        ];
        return response()->json($data, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        // cek jika produk ditemukan
        if ($product) {
            $request->validate([
                'name' => 'string|max:255',
                'price' => 'integer',
                'description' => 'string',
                'image' => 'nullable|image|mimes:png,jpg,webp,jpeg,svg|max:2048'
            ], ['price.integer' => 'Harga harus berupa angka']);
            $input = $request->all();

            if ($image = $request->file('image')) {
                $target = 'assets/images';
                // jika data image sebelumnya ada
                unlink($target . $product->image);
                $img_name = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $image->move($target, $img_name);
                $input['image'] = "$img_name"; // memasukkan nama
            } else {
                // jika tidak ada image
                $input['image'] = $product->image;
            }

            $product->update($input);
            $data = [
                'status' => Response::HTTP_OK,
                'message' => 'Data produk berhasil diupdate',
            ];
            return response()->json($data, Response::HTTP_OK);
        } else {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data produk tidak ditemukan',
            ];
            return response()->json($data, Response::HTTP_NOT_FOUND);
        }
    }
}
