<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/products",
     *      operationId="index",
     *      tags={"Product"},
     *      summary="Get all products",
     *      description="Get all products",
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="Get All products"),
     *              @OA\Property(property="data", type="array",
     *              @OA\Items(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="Product 1"),
     *              @OA\Property(property="price", type="integer", example=10000),
     *              @OA\Property(property="description", type="string", example="Description product 1"),
     *              @OA\Property(property="image", type="string", example="image.jpg"),
     *              )
     *            )
     *        )
     *     ),
     * )
     */
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

    /** @OA\Get(
     *      path="/api/products/{id}",
     *      operationId="show",
     *      tags={"Product"},
     *      summary="Get product by id",
     *      description="Get product by id",
     *      @OA\Parameter(
     *          name="id",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(response=200,description="Success",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/products",
     *      operationId="store",
     *      tags={"Product"},
     *      summary="Create new product",
     *      description="Create new product",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                type="object",
     *                required={"name", "price", "description", "image"},
     *                @OA\Property(property="name", type="string", example="Product 1"),
     *                @OA\Property(property="price", type="integer", example="10000"),
     *                @OA\Property(property="description", type="string", example="Description product 1"),
     *                @OA\Property(property="image", type="string", format="binary"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
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

    /**
     * @OA\Put(
     *      path="/api/products/{id}",
     *      operationId="update",
     *      tags={"Product"},
     *      summary="Update product by id",
     *      description="Update product by id",
     *      @OA\Parameter(
     *          name="id",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                type="object",
     *                required={"name", "price", "description", "image"},
     *                @OA\Property(property="name", type="string", example="Product 1"),
     *                @OA\Property(property="price", type="integer", example="10000"),
     *                @OA\Property(property="description", type="string", example="Description product 1"),
     *                @OA\Property(property="image", type="string", example="image.jpg"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
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
                $target = 'assets/images/';
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

    /**
     * @OA\Delete(
     *      path="/api/products/{id}",
     *      operationId="destroy",
     *      tags={"Product"},
     *      summary="Delete product by id",
     *      description="Delete product by id",
     *      @OA\Parameter(
     *          name="id",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product deleted successfully",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if ($product) {
            // jika ada gambar lama
            if ($product->image) {
                $target = 'assets/images/';
                unlink($target . $product->image);
            }
            $product->delete();
            $data = [
                'status' => Response::HTTP_OK,
                'message' => 'Data produk berhasil dihapus',
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
