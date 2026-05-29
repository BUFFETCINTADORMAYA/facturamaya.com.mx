"""Yaxtun-OmniCore - API REST Completa
Gestión de diagnósticos, reparaciones, órdenes de trabajo y comunicaciones"""

from flask import Flask, jsonify, request, send_file, render_template
from flask_cors import CORS
from flask_socketio import SocketIO, emit
import json
import os
from datetime import datetime, timedelta
import logging
import subprocess
from pathlib import Path
import sys

# Importar módulos del sistema
sys.path.insert(0, '/app/core')
sys.path.insert(0, '/app/modules')

try:
    from ai_diagnostics import AIDiagnosticsEngine
    from device_detector import DeviceDetector
    from ios_device_detector import iOSDeviceDetector
    from ios_repair_tools import iOSRepairTools
    from pdf_generator import PDFGenerator
    from communicator import EmailCommunicator, WhatsAppCommunicator
except ImportError as e:
    print(f"Advertencia: No se pudieron importar módulos: {e}")

app = Flask(__name__)
CORS(app)
socketio = SocketIO(app, cors_allowed_origins="*")

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Inicializar motores
ai_engine = None
device_detector = None
ios_detector = None
ios_tools = None
pdf_gen = None
email_comm = None
whatsapp_comm = None

def init_engines():
    """Inicializar todos los motores del sistema"""
    global ai_engine, device_detector, ios_detector, ios_tools, pdf_gen, email_comm, whatsapp_comm
    try:
        ai_engine = AIDiagnosticsEngine()
        device_detector = DeviceDetector()
        ios_detector = iOSDeviceDetector()
        ios_tools = iOSRepairTools()
        pdf_gen = PDFGenerator()
        email_comm = EmailCommunicator()
        whatsapp_comm = WhatsAppCommunicator()
        logger.info("✓ Todos los motores inicializados correctamente")
    except Exception as e:
        logger.warning(f"Advertencia al inicializar motores: {e}")

# Rutas de Health
@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        "status": "ok",
        "app": "Yaxtun-OmniCore",
        "version": "2.0.0"
    }), 200

# Rutas de Versión
@app.route('/api/version', methods=['GET'])
def get_version():
    return jsonify({
        "app": "Yaxtun-OmniCore",
        "version": "2.0.0",
        "features": [
            "Diagnóstico Android + iOS con IA",
            "Flasheo de ROM",
            "Desbloqueo FRP",
            "Face ID Preservation",
            "Órdenes de Trabajo",
            "Generación de PDF",
            "Envío Email/WhatsApp",
            "Reparación Inteligente"
        ],
        "timestamp": datetime.now().isoformat()
    }), 200

# ===== ANDROID =====

@app.route('/api/android/devices', methods=['GET'])
def get_android_devices():
    """Obtener dispositivos Android conectados"""
    try:
        if device_detector:
            devices = device_detector.get_all_devices()
            return jsonify({
                "status": "success",
                "devices": devices,
                "count": len(devices)
            }), 200
    except Exception as e:
        logger.error(f"Error obteniendo dispositivos: {e}")
    
    return jsonify({"devices": [], "count": 0}), 200

@app.route('/api/android/diagnose', methods=['POST'])
def diagnose_android():
    """Diagnosticar dispositivo Android"""
    data = request.json
    try:
        if ai_engine and device_detector:
            device_info = device_detector.get_device_info(data.get('udid', ''))
            diagnosis = ai_engine.diagnose_device(device_info)
            
            return jsonify({
                "status": "success",
                "diagnosis": diagnosis
            }), 200
    except Exception as e:
        logger.error(f"Error en diagnóstico: {e}")
    
    return jsonify({"status": "error"}), 400

@app.route('/api/android/flash', methods=['POST'])
def flash_android():
    """Flashear ROM en Android"""
    data = request.json
    return jsonify({
        "status": "initiated",
        "message": "Flasheo iniciado",
        "device": data.get('udid')
    }), 200

# ===== iOS =====

@app.route('/api/ios/devices', methods=['GET'])
def get_ios_devices():
    """Obtener dispositivos iOS conectados"""
    try:
        if ios_detector:
            devices = ios_detector.get_all_devices()
            return jsonify({
                "status": "success",
                "devices": devices,
                "count": len(devices)
            }), 200
    except Exception as e:
        logger.error(f"Error obteniendo dispositivos iOS: {e}")
    
    return jsonify({"devices": [], "count": 0}), 200

@app.route('/api/ios/diagnose', methods=['POST'])
def diagnose_ios():
    """Diagnosticar iPhone"""
    data = request.json
    udid = data.get('udid')
    
    try:
        if ios_detector:
            device_info = ios_detector.get_device_full_info(udid)
            diagnosis = ai_engine.diagnose_device(device_info) if ai_engine else {}
            display_diag = ios_detector.diagnose_display_components(udid)
            
            return jsonify({
                "status": "success",
                "device_info": device_info,
                "diagnosis": diagnosis,
                "display_components": display_diag
            }), 200
    except Exception as e:
        logger.error(f"Error en diagnóstico iOS: {e}")
    
    return jsonify({"status": "error"}), 400

@app.route('/api/ios/extract-components', methods=['POST'])
def extract_ios_components():
    """Extraer componentes de iPhone (Face ID, Batería, etc)"""
    data = request.json
    udid = data.get('udid')
    components = data.get('components', ['all'])
    
    try:
        if ios_tools:
            result = ios_tools.extract_component_serials(udid, components)
            return jsonify({
                "status": "success",
                "extraction": result
            }), 200
    except Exception as e:
        logger.error(f"Error extrayendo componentes: {e}")
    
    return jsonify({"status": "error"}), 400

@app.route('/api/ios/restore-components', methods=['POST'])
def restore_ios_components():
    """Restaurar componentes en iPhone nuevo"""
    data = request.json
    udid = data.get('udid')
    backup_file = data.get('backup_file')
    
    try:
        if ios_tools:
            result = ios_tools.restore_component_data(udid, backup_file)
            return jsonify({
                "status": "success",
                "restoration": result
            }), 200
    except Exception as e:
        logger.error(f"Error restaurando componentes: {e}")
    
    return jsonify({"status": "error"}), 400

# ===== ÓRDENES DE TRABAJO =====

@app.route('/api/orders', methods=['GET', 'POST'])
def manage_orders():
    """Crear u obtener órdenes de trabajo"""
    if request.method == 'POST':
        order_data = request.json
        order_id = f"ORD-{datetime.now().strftime('%Y%m%d%H%M%S')}"
        
        return jsonify({
            "status": "created",
            "order_id": order_id,
            "order_data": order_data
        }), 201
    
    return jsonify({"orders": []}), 200

# ===== PDF =====

@app.route('/api/pdf/generate-diagnostic', methods=['POST'])
def generate_diagnostic_pdf():
    """Generar PDF de diagnóstico"""
    data = request.json
    
    try:
        if pdf_gen:
            pdf_file = pdf_gen.generate_diagnostic_pdf(
                device_info=data.get('device_info'),
                diagnosis=data.get('diagnosis'),
                client_data=data.get('client_data')
            )
            
            return jsonify({
                "status": "success",
                "pdf_file": pdf_file,
                "download_url": f"/api/pdf/download/{os.path.basename(pdf_file)}"
            }), 200
    except Exception as e:
        logger.error(f"Error generando PDF: {e}")
    
    return jsonify({"status": "error"}), 400

@app.route('/api/pdf/generate-repair-order', methods=['POST'])
def generate_repair_order_pdf():
    """Generar PDF de orden de reparación"""
    data = request.json
    
    try:
        if pdf_gen:
            pdf_file = pdf_gen.generate_repair_order_pdf(
                order_id=data.get('order_id'),
                device_info=data.get('device_info'),
                services=data.get('services'),
                costs=data.get('costs'),
                client_data=data.get('client_data')
            )
            
            return jsonify({
                "status": "success",
                "pdf_file": pdf_file,
                "download_url": f"/api/pdf/download/{os.path.basename(pdf_file)}"
            }), 200
    except Exception as e:
        logger.error(f"Error generando orden: {e}")
    
    return jsonify({"status": "error"}), 400

@app.route('/api/pdf/download/<filename>', methods=['GET'])
def download_pdf(filename):
    """Descargar PDF generado"""
    try:
        filepath = f"/app/database/pdfs/{filename}"
        if os.path.exists(filepath):
            return send_file(filepath, as_attachment=True)
    except Exception as e:
        logger.error(f"Error descargando PDF: {e}")
    
    return jsonify({"status": "error"}), 404

# ===== COMUNICACIONES =====

@app.route('/api/send/email', methods=['POST'])
def send_email():
    """Enviar PDF por Email"""
    data = request.json
    
    try:
        if email_comm:
            result = email_comm.send_pdf(
                to_email=data.get('email'),
                subject=data.get('subject'),
                pdf_file=data.get('pdf_file'),
                message=data.get('message')
            )
            
            return jsonify({
                "status": "sent",
                "destination": data.get('email')
            }), 200
    except Exception as e:
        logger.error(f"Error enviando email: {e}")
    
    return jsonify({"status": "error"}), 400

@app.route('/api/send/whatsapp', methods=['POST'])
def send_whatsapp():
    """Enviar PDF por WhatsApp"""
    data = request.json
    
    try:
        if whatsapp_comm:
            result = whatsapp_comm.send_pdf(
                phone=data.get('phone'),
                message=data.get('message'),
                pdf_file=data.get('pdf_file')
            )
            
            return jsonify({
                "status": "sent",
                "destination": data.get('phone')
            }), 200
    except Exception as e:
        logger.error(f"Error enviando WhatsApp: {e}")
    
    return jsonify({"status": "error"}), 400

# ===== DASHBOARD =====

@app.route('/', methods=['GET'])
@app.route('/index', methods=['GET'])
def dashboard():
    """Dashboard principal"""
    return jsonify({
        "status": "ok",
        "message": "Yaxtun-OmniCore v2.0.0 - Sistema de Reparación de Dispositivos Móviles",
        "endpoints": {
            "Android": {
                "devices": "GET /api/android/devices",
                "diagnose": "POST /api/android/diagnose",
                "flash": "POST /api/android/flash"
            },
            "iOS": {
                "devices": "GET /api/ios/devices",
                "diagnose": "POST /api/ios/diagnose",
                "extract_components": "POST /api/ios/extract-components",
                "restore_components": "POST /api/ios/restore-components"
            },
            "Órdenes": {
                "create": "POST /api/orders",
                "list": "GET /api/orders"
            },
            "PDF": {
                "diagnostic": "POST /api/pdf/generate-diagnostic",
                "repair_order": "POST /api/pdf/generate-repair-order",
                "download": "GET /api/pdf/download/<filename>"
            },
            "Comunicaciones": {
                "email": "POST /api/send/email",
                "whatsapp": "POST /api/send/whatsapp"
            }
        }
    }), 200

@app.errorhandler(404)
def not_found(error):
    return jsonify({"error": "Endpoint no encontrado"}), 404

@app.errorhandler(500)
def internal_error(error):
    return jsonify({"error": "Error interno del servidor"}), 500

if __name__ == '__main__':
    logger.info("="*60)
    logger.info("🌟 YAXTUN-OmniCore v2.0.0")
    logger.info("Sistema Profesional de Reparación de Dispositivos Móviles")
    logger.info("="*60)
    
    init_engines()
    
    logger.info("🚀 Iniciando API REST en http://0.0.0.0:5000")
    socketio.run(app, host='0.0.0.0', port=5000, debug=False, allow_unsafe_werkzeug=True)
