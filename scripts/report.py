#!/usr/bin/env python3
# scripts/report.py
# Analyzes reservation and guest data from Hotel Luna Azul

import sys
import json
from datetime import datetime

def generate_report(data_file):
    try:
        with open(data_file, 'r', encoding='utf-8') as f:
            reservations = json.load(f)
    except Exception as e:
        print(f"Error al abrir o leer el archivo de datos: {e}")
        return

    total_records = len(reservations)
    if total_records == 0:
        print("==================================================")
        # Styled terminal output
        print("      HOTEL LUNA AZUL - INFORME ANALÍTICO")
        print("==================================================")
        print("[-] No se encontraron reservas para analizar.")
        print("==================================================")
        return

    # Count statuses
    confirmed_count = sum(1 for r in reservations if r['estado'] == 'Confirmada')
    cancelled_count = sum(1 for r in reservations if r['estado'] == 'Cancelada')

    confirmed_pct = (confirmed_count / total_records) * 100 if total_records > 0 else 0
    cancelled_pct = (cancelled_count / total_records) * 100 if total_records > 0 else 0

    # Active reservations metrics
    active_reservations = [r for r in reservations if r['estado'] == 'Confirmada']
    
    total_nights = 0
    total_huespedes = 0
    total_age = 0
    age_count = 0
    
    age_groups = {
        'Menores (0-17 años)': 0,
        'Jóvenes (18-30 años)': 0,
        'Adultos (31-50 años)': 0,
        'Mayores (51+ años)': 0
    }
    
    room_types = {}

    for res in active_reservations:
        # Calculate stay nights
        try:
            d_in = datetime.strptime(res['fecha_ingreso'], '%Y-%m-%d')
            d_out = datetime.strptime(res['fecha_salida'], '%Y-%m-%d')
            nights = (d_out - d_in).days
            total_nights += max(0, nights)
        except Exception:
            pass

        # Calculate guests count
        total_huespedes += int(res.get('numero_huespedes', 1))

        # Demographic distribution
        age = res.get('edad')
        if age is not None:
            age = int(age)
            total_age += age
            age_count += 1
            if age < 18:
                age_groups['Menores (0-17 años)'] += 1
            elif age <= 30:
                age_groups['Jóvenes (18-30 años)'] += 1
            elif age <= 50:
                age_groups['Adultos (31-50 años)'] += 1
            else:
                age_groups['Mayores (51+ años)'] += 1

        # Room types distribution
        t_room = res.get('tipo_habitacion', 'Desconocido')
        room_types[t_room] = room_types.get(t_room, 0) + 1

    avg_nights = total_nights / confirmed_count if confirmed_count > 0 else 0
    avg_age = total_age / age_count if age_count > 0 else 0

    # Print Report
    print("==================================================")
    print("   HOTEL LUNA AZUL - REPORTE DE ANALÍTICA M.V.C. ")
    print("==================================================")
    print(f"Generado el: {datetime.now().strftime('%d/%m/%Y %H:%M:%S')}")
    print(f"Motor de Análisis: Python {sys.version.split()[0]}")
    print("--------------------------------------------------")
    print(f"[+] Total Reservas Analizadas: {total_records}")
    print(f"    - Confirmadas : {confirmed_count} ({confirmed_pct:.1f}%)")
    print(f"    - Canceladas  : {cancelled_count} ({cancelled_pct:.1f}%)")
    print("--------------------------------------------------")
    print("📋 ESTADÍSTICAS DE ESTADÍA (Reservas Confirmadas)")
    print(f"    - Total Noches Reservadas : {total_nights} noches")
    print(f"    - Duración Promedio       : {avg_nights:.1f} noches por reserva")
    print(f"    - Total Huéspedes Alojar  : {total_huespedes} personas")
    print("--------------------------------------------------")
    print("👥 DEMOGRAFÍA DE HUÉSPEDES")
    if age_count > 0:
        print(f"    - Edad Promedio de Huéspedes : {avg_age:.1f} años")
        print("    - Distribución por Grupos de Edad:")
        for group, count in age_groups.items():
            pct = (count / age_count) * 100 if age_count > 0 else 0
            print(f"      * {group:<22}: {count} ({pct:.1f}%)")
    else:
        print("    - No hay datos de edad disponibles.")
    print("--------------------------------------------------")
    print("🏨 DEMANDA POR TIPO DE HABITACIÓN")
    if room_types:
        for rtype, count in room_types.items():
            pct = (count / confirmed_count) * 100 if confirmed_count > 0 else 0
            print(f"    - Hab. {rtype:<15}: {count} reservadas ({pct:.1f}%)")
    else:
        print("    - No hay reservas activas en habitaciones.")
    print("==================================================")

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print("Uso: python3 report.py <ruta_del_archivo_json>")
    else:
        generate_report(sys.argv[1])
