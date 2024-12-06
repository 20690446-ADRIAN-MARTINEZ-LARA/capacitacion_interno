<?php

namespace App\Http\Controllers;

use App\Models\cursos_instructore;
use App\Models\cursos_participante;
use App\Models\Departamento;
use App\Models\Participante;
use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::all();
        return view('vistas.usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Lógica de creación si es necesario
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $usuario = User::findOrFail($id);
            $participante = Participante::find($id);
            $cursos = cursos_participante::with('curso')->where('participante_id', $participante->id)->orderBy('id', 'desc')->get();

            return view('vistas.usuarios.show', compact('usuario', 'cursos'));
        } catch (\Exception $e) {
            // Si ocurre un error, redirigir al inicio con el mensaje de error
            return redirect()->route('inicio')->with('error', 'Usuario no encontrado o error al cargar los datos.');
        }
    }

    public function edit(string $id)
    {
        try {
            $usuario = User::findOrFail($id);
            $departamentos = Departamento::all();
            $roles = Role::all();

            return view('vistas.usuarios.edit', compact('usuario', 'departamentos', 'roles'));
        } catch (\Exception $e) {
            // Si ocurre un error, redirigir al inicio con el mensaje de error
            return redirect()->route('inicio')->with('error', 'Error al cargar los datos del usuario.');
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validación de los datos de entrada
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidoP' => 'nullable|string|max:255',
            'apellidoM' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'curp' => 'nullable|string|max:18',
            'rfc' => 'nullable|string|max:13',
            'telefono' => 'nullable|string|max:15',
            'departamento' => 'required|exists:departamentos,id',
            'tipo_usuario' => 'required|integer|in:1,2,3',
            'email' => 'required|email|unique:users,email,' . $id, // Excluir el email actual de la validación
            'password' => 'nullable|string|min:8|confirmed', // La contraseña es opcional, pero debe ser confirmada si se incluye
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id', // Validación para cada rol
        ]);

        // Actualizar el usuario
        $usuario = User::findOrFail($id);
        $usuario->email = $validated['email'];
        $usuario->tipo = $validated['tipo_usuario'];

        if ($request->filled('password')) {
            $usuario->password = Hash::make($validated['password']);
        }
        $usuario->save();

        // Actualizar los datos generales
        $datosGenerales = $usuario->datos_generales;
        $datosGenerales->nombre = $validated['nombre'];
        $datosGenerales->apellido_paterno = $validated['apellidoP'];
        $datosGenerales->apellido_materno = $validated['apellidoM'];
        $datosGenerales->fecha_nacimiento = $validated['fecha_nacimiento'];
        $datosGenerales->curp = $validated['curp'];
        $datosGenerales->rfc = $validated['rfc'];
        $datosGenerales->telefono = $validated['telefono'];
        $datosGenerales->departamento_id = $validated['departamento'];
        $datosGenerales->save();

        // Asignar roles al usuario
        $role_instructor = Role::where('nombre', 'Instructor')->first();
        if ($request->filled('roles')) {
            $usuario->roles()->sync($validated['roles']);

            if (in_array($role_instructor->id, $validated['roles'])) {
                // Si el usuario no tiene un registro de instructor, lo crea
                if (!$usuario->instructor()->exists()) {
                    $usuario->instructor()->create(['user_id' => $usuario->id]);
                }
            }
        } else {
            $usuario->roles()->detach();
        }

        return Redirect::route('usuario_datos.index', $usuario->id)->with('status', 'Usuario actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function activar($id)
    {
        try {
            $usuario = User::findOrFail($id);
            $usuario->estatus = true;
            $usuario->save();

            return redirect(route('usuario_datos.index', $id))->with('success', 'Usuario activado correctamente');
        } catch (\Exception $e) {
            // Si ocurre un error, redirigir al inicio con el mensaje de error
            return redirect()->route('inicio')->with('error', 'Error al activar el usuario.');
        }
    }

    public function desactivar($id)
    {
        try {
            $usuario = User::findOrFail($id);
            $usuario->estatus = false;
            $usuario->save();

            return redirect(route('usuario_datos.index', $id))->with('success', 'Usuario desactivado correctamente');
        } catch (\Exception $e) {
            // Si ocurre un error, redirigir al inicio con el mensaje de error
            return redirect()->route('inicio')->with('error', 'Error al desactivar el usuario.');
        }
    }
}
