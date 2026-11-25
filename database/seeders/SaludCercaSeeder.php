<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use App\Models\Horario;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de EJEMPLO para la demostración.
 * Los nombres y las coordenadas son aproximados: ajústalos con datos reales si lo deseas.
 */
class SaludCercaSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Usuarios de prueba (contraseña: password123) ----------
        $admin = User::forceCreate([
            'name' => 'Administrador SaludCerca', 'email' => 'admin@saludcerca.com',
            'password' => Hash::make('password123'), 'rol' => 'administrador', 'activo' => true,
        ]);
        $rep = User::forceCreate([
            'name' => 'Representante Demo', 'email' => 'representante@saludcerca.com',
            'password' => Hash::make('password123'), 'rol' => 'representante', 'activo' => true,
        ]);
        User::forceCreate([
            'name' => 'Ciudadano Demo', 'email' => 'ciudadano@saludcerca.com',
            'password' => Hash::make('password123'), 'rol' => 'ciudadano', 'activo' => true,
        ]);

        // ---------- Servicios ----------
        $nombres = ['Atención 24 horas', 'Emergencias', 'Consulta general', 'Pediatría', 'Laboratorio',
                    'Rayos X', 'Vacunación', 'Inyecciones', 'Entrega a domicilio', 'Medicamentos genéricos'];
        $servicios = [];
        foreach ($nombres as $n) {
            $servicios[$n] = Servicio::create(['nombre' => $n]);
        }

        // ---------- Establecimientos (ejemplo) ----------
        // [tipo, tipo_centro, nombre, dirección, teléfono, lat, lng, estado, 24h?, servicios]
        $lista = [
            ['farmacia', null, 'Farmacia Plaza Central', 'Calle Libertad esq. Ballivián, Centro', '33320001', -17.7836, -63.1822, 'activo', false, ['Inyecciones', 'Medicamentos genéricos']],
            ['farmacia', null, 'Farmacia San Martín', 'Av. Cañoto, 2do anillo', '33320002', -17.7762, -63.1895, 'activo', true, ['Atención 24 horas', 'Inyecciones', 'Entrega a domicilio']],
            ['farmacia', null, 'Farmacia El Remedio', 'Av. Irala esq. Sucre', '33320003', -17.7871, -63.1793, 'activo', false, ['Medicamentos genéricos']],
            ['farmacia', null, 'Farmacia La Salud', 'Av. San Martín, Equipetrol', '33320004', -17.7585, -63.1976, 'activo', true, ['Atención 24 horas', 'Entrega a domicilio']],
            ['farmacia', null, 'Farmacia Santa Rita', 'Av. Banzer, 4to anillo', '33320005', -17.7480, -63.1710, 'activo', false, ['Inyecciones']],
            ['farmacia', null, 'Farmacia Mercado Los Pozos', 'Calle Isabel la Católica, Los Pozos', '33320006', -17.7905, -63.1685, 'activo', false, ['Medicamentos genéricos', 'Inyecciones']],
            ['farmacia', null, 'Farmacia Plan 3000', 'Av. Che Guevara, Plan 3000', '33320007', -17.8245, -63.1325, 'activo', false, ['Medicamentos genéricos']],
            ['farmacia', null, 'Farmacia Nueva Esperanza', 'Av. Virgen de Cotoca, 3er anillo', '33320008', -17.7650, -63.1450, 'activo', false, ['Inyecciones']],
            ['farmacia', null, 'Farmacia Villa 1ro de Mayo', 'Av. Roca y Coronado', '33320009', -17.8005, -63.1135, 'pendiente_validacion', false, ['Medicamentos genéricos']],
            ['centro_salud', 'hospital', 'Hospital Universitario (ejemplo)', 'Av. Japón, 3er anillo', '3-3460001', -17.7595, -63.1650, 'activo', true, ['Emergencias', 'Consulta general', 'Laboratorio', 'Rayos X']],
            ['centro_salud', 'hospital', 'Hospital San Juan de Dios (ejemplo)', 'Calle Sara, Centro', '3-3460002', -17.7810, -63.1855, 'activo', true, ['Emergencias', 'Consulta general', 'Pediatría']],
            ['centro_salud', 'centro_de_salud', 'Centro de Salud Los Pozos', 'Calle Los Pozos', '3-3460003', -17.7918, -63.1700, 'activo', false, ['Consulta general', 'Vacunación']],
            ['centro_salud', 'centro_de_salud', 'Centro de Salud Plan 3000', 'Plan 3000, Zona Norte', '3-3460004', -17.8210, -63.1300, 'activo', false, ['Consulta general', 'Pediatría', 'Vacunación']],
            ['centro_salud', 'clinica', 'Clínica Equipetrol (ejemplo)', 'Av. Equipetrol', '3-3460005', -17.7600, -63.1990, 'activo', true, ['Emergencias', 'Consulta general', 'Laboratorio']],
            ['centro_salud', 'clinica', 'Clínica Santa Cruz (ejemplo)', 'Av. Pirai, 3er anillo', '3-3460006', -17.7700, -63.1600, 'activo', false, ['Consulta general', 'Pediatría', 'Rayos X']],
            ['centro_salud', 'centro_de_salud', 'Centro de Salud Villa 1ro de Mayo', 'Av. Principal, Villa 1ro de Mayo', '3-3460007', -17.8030, -63.1120, 'activo', false, ['Consulta general', 'Vacunación']],
        ];

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $farmaciasActivas = [];

        foreach ($lista as $i => [$tipo, $tc, $nombre, $dir, $tel, $lat, $lng, $estado, $h24, $srv]) {
            $e = Establecimiento::create([
                'representante_id' => $i === 0 ? $rep->id : null, // la primera farmacia es del representante demo
                'tipo' => $tipo, 'tipo_centro' => $tc, 'nombre' => $nombre, 'direccion' => $dir,
                'telefono' => $tel, 'latitud' => $lat, 'longitud' => $lng, 'estado' => $estado,
            ]);

            foreach ($dias as $d) {
                if ($h24) {
                    $ap = '00:00:00'; $ci = '23:59:00';
                } elseif ($d === 'domingo') {
                    $ap = '09:00:00'; $ci = '13:00:00';
                } else {
                    $ap = '08:00:00'; $ci = '20:00:00';
                }
                Horario::create(['establecimiento_id' => $e->id, 'dia_semana' => $d, 'hora_apertura' => $ap, 'hora_cierre' => $ci]);
            }

            $e->servicios()->attach(array_map(fn ($n) => $servicios[$n]->id, $srv));

            if ($tipo === 'farmacia' && $estado === 'activo') {
                $farmaciasActivas[] = $e;
            }
        }

        // ---------- Turnos de farmacia vigentes (alrededor de hoy) ----------
        foreach (array_slice($farmaciasActivas, 0, 3) as $k => $f) {
            Turno::create([
                'establecimiento_id' => $f->id,
                'fecha_inicio' => now('America/La_Paz')->subDay()->toDateString(),
                'fecha_fin' => now('America/La_Paz')->addDays(2 + $k)->toDateString(),
            ]);
        }
    }
}
