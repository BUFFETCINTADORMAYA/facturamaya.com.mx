"""Comunicador de Email y WhatsApp
Envío de PDF y notificaciones por múltiples canales"""

import os
import logging
from datetime import datetime

logger = logging.getLogger(__name__)

class EmailCommunicator:
    """Envío de emails con PDF adjuntos"""
    
    def __init__(self):
        self.smtp_server = os.getenv('SMTP_SERVER', 'smtp.gmail.com')
        self.smtp_port = int(os.getenv('SMTP_PORT', 587))
        self.sender_email = os.getenv('SENDER_EMAIL', 'soporte@yaxtun.com')
        self.sender_password = os.getenv('SENDER_PASSWORD', '')
    
    def send_pdf(self, to_email, subject, pdf_file, message):
        """Enviar PDF por email"""
        try:
            import smtplib
            from email.mime.multipart import MIMEMultipart
            from email.mime.base import MIMEBase
            from email.mime.text import MIMEText
            from email import encoders
            
            # Crear mensaje
            msg = MIMEMultipart()
            msg['From'] = self.sender_email
            msg['To'] = to_email
            msg['Subject'] = subject
            
            # Cuerpo del mensaje
            msg.attach(MIMEText(message, 'html'))
            
            # Adjuntar PDF
            if os.path.exists(pdf_file):
                with open(pdf_file, 'rb') as attachment:
                    part = MIMEBase('application', 'octet-stream')
                    part.set_payload(attachment.read())
                    encoders.encode_base64(part)
                    part.add_header('Content-Disposition', f'attachment; filename= {os.path.basename(pdf_file)}')
                    msg.attach(part)
            
            # Enviar
            if self.sender_password:
                server = smtplib.SMTP(self.smtp_server, self.smtp_port)
                server.starttls()
                server.login(self.sender_email, self.sender_password)
                server.send_message(msg)
                server.quit()
                
                logger.info(f"Email enviado a {to_email}")
                return {"status": "sent", "email": to_email}
            else:
                logger.warning("Credenciales de email no configuradas")
                return {"status": "error", "message": "Email no configurado"}
                
        except Exception as e:
            logger.error(f"Error enviando email: {e}")
            return {"status": "error", "message": str(e)}

class WhatsAppCommunicator:
    """Envío de mensajes y PDF por WhatsApp"""
    
    def __init__(self):
        self.api_key = os.getenv('WHATSAPP_API_KEY', '')
        self.api_url = os.getenv('WHATSAPP_API_URL', 'https://api.whatsapp.com/send')
    
    def send_pdf(self, phone, message, pdf_file):
        """Enviar PDF por WhatsApp"""
        try:
            import requests
            
            # Crear enlace de descarga del PDF
            pdf_filename = os.path.basename(pdf_file)
            download_url = f"http://localhost:5000/api/pdf/download/{pdf_filename}"
            
            # Formatear mensaje
            whatsapp_message = f"{message}\n\n📄 Descarga tu PDF: {download_url}"
            
            # En producción, usar Twilio o similar
            # Por ahora, registrar la intención
            logger.info(f"WhatsApp preparado para enviar a {phone}: {whatsapp_message}")
            
            return {
                "status": "queued",
                "phone": phone,
                "pdf_download_url": download_url
            }
            
        except Exception as e:
            logger.error(f"Error enviando WhatsApp: {e}")
            return {"status": "error", "message": str(e)}
    
    def send_message(self, phone, message):
        """Enviar mensaje simple por WhatsApp"""
        try:
            logger.info(f"Mensaje WhatsApp para {phone}: {message}")
            return {"status": "queued", "phone": phone}
        except Exception as e:
            logger.error(f"Error: {e}")
            return {"status": "error"}
