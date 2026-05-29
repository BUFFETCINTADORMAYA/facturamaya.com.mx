"""Generador de PDF Profesional
Crea PDF de diagnóstico y órdenes de trabajo personalizables"""

import os
from datetime import datetime
from reportlab.lib.pagesizes import letter, A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer, Image, PageBreak
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT, TA_JUSTIFY
import logging

logger = logging.getLogger(__name__)

class PDFGenerator:
    """Generador de PDF para órdenes de trabajo y diagnósticos"""
    
    def __init__(self):
        self.pdf_dir = "/app/database/pdfs"
        os.makedirs(self.pdf_dir, exist_ok=True)
        self.styles = getSampleStyleSheet()
        self._add_custom_styles()
    
    def _add_custom_styles(self):
        """Agregar estilos personalizados"""
        # Título
        self.styles.add(ParagraphStyle(
            name='CustomTitle',
            parent=self.styles['Heading1'],
            fontSize=24,
            textColor=colors.HexColor('#1a73e8'),
            spaceAfter=30,
            alignment=TA_CENTER,
            fontName='Helvetica-Bold'
        ))
        
        # Subtítulo
        self.styles.add(ParagraphStyle(
            name='CustomSubtitle',
            parent=self.styles['Heading2'],
            fontSize=14,
            textColor=colors.HexColor('#4285f4'),
            spaceAfter=12,
            fontName='Helvetica-Bold'
        ))
        
        # Texto normal
        self.styles.add(ParagraphStyle(
            name='CustomBody',
            parent=self.styles['BodyText'],
            fontSize=10,
            alignment=TA_JUSTIFY
        ))
    
    def generate_diagnostic_pdf(self, device_info, diagnosis, client_data, logo_path=None):
        """Generar PDF de diagnóstico"""
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        pdf_filename = f"Diagnostico_{device_info.get('model', 'Unknown')}_{timestamp}.pdf"
        pdf_path = os.path.join(self.pdf_dir, pdf_filename)
        
        try:
            doc = SimpleDocTemplate(pdf_path, pagesize=letter)
            elements = []
            
            # Logo y encabezado
            if logo_path and os.path.exists(logo_path):
                img = Image(logo_path, width=1*inch, height=1*inch)
                elements.append(img)
            
            # Título
            elements.append(Paragraph("DIAGNÓSTICO DE DISPOSITIVO MÓVIL", self.styles['CustomTitle']))
            elements.append(Spacer(1, 0.2*inch))
            
            # Información del cliente
            elements.append(Paragraph("Información del Cliente", self.styles['CustomSubtitle']))
            client_table = [
                ['Nombre:', client_data.get('name', 'N/A')],
                ['Teléfono:', client_data.get('phone', 'N/A')],
                ['Email:', client_data.get('email', 'N/A')]
            ]
            client_table_obj = Table(client_table, colWidths=[2*inch, 4*inch])
            client_table_obj.setStyle(TableStyle([
                ('BACKGROUND', (0, 0), (0, -1), colors.lightgrey),
                ('TEXTCOLOR', (0, 0), (-1, -1), colors.black),
                ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
                ('FONTNAME', (0, 0), (0, -1), 'Helvetica-Bold'),
                ('FONTSIZE', (0, 0), (-1, -1), 10),
                ('BOTTOMPADDING', (0, 0), (-1, -1), 12),
                ('GRID', (0, 0), (-1, -1), 1, colors.black)
            ]))
            elements.append(client_table_obj)
            elements.append(Spacer(1, 0.3*inch))
            
            # Información del dispositivo
            elements.append(Paragraph("Información del Dispositivo", self.styles['CustomSubtitle']))
            device_table = [
                ['Modelo:', device_info.get('model', 'N/A')],
                ['Marca:', device_info.get('brand', 'N/A')],
                ['Serial:', device_info.get('serial_number', 'N/A')],
                ['Sistema Operativo:', device_info.get('ios_version', device_info.get('android_version', 'N/A'))]
            ]
            device_table_obj = Table(device_table, colWidths=[2*inch, 4*inch])
            device_table_obj.setStyle(TableStyle([
                ('BACKGROUND', (0, 0), (0, -1), colors.lightgrey),
                ('TEXTCOLOR', (0, 0), (-1, -1), colors.black),
                ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
                ('FONTNAME', (0, 0), (0, -1), 'Helvetica-Bold'),
                ('FONTSIZE', (0, 0), (-1, -1), 10),
                ('BOTTOMPADDING', (0, 0), (-1, -1), 12),
                ('GRID', (0, 0), (-1, -1), 1, colors.black)
            ]))
            elements.append(device_table_obj)
            elements.append(Spacer(1, 0.3*inch))
            
            # Resultados del diagnóstico
            elements.append(Paragraph("Resultados del Diagnóstico", self.styles['CustomSubtitle']))
            
            findings = diagnosis.get('findings', [])
            if findings:
                findings_data = [['Componente', 'Estado', 'Descripción']]
                for finding in findings:
                    status_emoji = '🔴' if finding.get('status') == 'critical' else '🟡' if finding.get('status') == 'warning' else '🟢'
                    findings_data.append([
                        finding.get('component', 'N/A'),
                        status_emoji,
                        finding.get('message', 'N/A')
                    ])
                
                findings_table = Table(findings_data, colWidths=[1.5*inch, 1*inch, 3.5*inch])
                findings_table.setStyle(TableStyle([
                    ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#1a73e8')),
                    ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
                    ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
                    ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
                    ('FONTSIZE', (0, 0), (-1, -1), 9),
                    ('BOTTOMPADDING', (0, 0), (-1, -1), 12),
                    ('GRID', (0, 0), (-1, -1), 1, colors.black),
                    ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.lightgrey])
                ]))
                elements.append(findings_table)
            
            elements.append(Spacer(1, 0.3*inch))
            
            # Recomendaciones
            recommendations = diagnosis.get('recommendations', [])
            if recommendations:
                elements.append(Paragraph("Recomendaciones", self.styles['CustomSubtitle']))
                for i, rec in enumerate(recommendations, 1):
                    elements.append(Paragraph(f"• {rec}", self.styles['CustomBody']))
            
            elements.append(Spacer(1, 0.5*inch))
            
            # Firma del técnico
            elements.append(Paragraph("_" * 50, self.styles['CustomBody']))
            elements.append(Paragraph("Firma del Técnico", self.styles['CustomBody']))
            elements.append(Paragraph(f"Fecha: {datetime.now().strftime('%d/%m/%Y %H:%M')}", self.styles['CustomBody']))
            
            # Construir PDF
            doc.build(elements)
            logger.info(f"PDF de diagnóstico creado: {pdf_filename}")
            return pdf_path
            
        except Exception as e:
            logger.error(f"Error generando PDF de diagnóstico: {e}")
            return None
    
    def generate_repair_order_pdf(self, order_id, device_info, services, costs, client_data, logo_path=None, signature_line=True):
        """Generar PDF de orden de reparación"""
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        pdf_filename = f"Orden_Reparacion_{order_id}_{timestamp}.pdf"
        pdf_path = os.path.join(self.pdf_dir, pdf_filename)
        
        try:
            doc = SimpleDocTemplate(pdf_path, pagesize=letter)
            elements = []
            
            # Logo y encabezado
            if logo_path and os.path.exists(logo_path):
                img = Image(logo_path, width=1*inch, height=1*inch)
                elements.append(img)
            
            # Título
            elements.append(Paragraph("ORDEN DE REPARACIÓN", self.styles['CustomTitle']))
            elements.append(Spacer(1, 0.2*inch))
            
            # Número de orden
            elements.append(Paragraph(f"Orden ID: {order_id}", self.styles['CustomSubtitle']))
            elements.append(Spacer(1, 0.2*inch))
            
            # Información del cliente
            elements.append(Paragraph("Información del Cliente", self.styles['CustomSubtitle']))
            client_table = [
                ['Nombre:', client_data.get('name', 'N/A')],
                ['Teléfono:', client_data.get('phone', 'N/A')],
                ['Email:', client_data.get('email', 'N/A')]
            ]
            client_table_obj = Table(client_table, colWidths=[2*inch, 4*inch])
            client_table_obj.setStyle(TableStyle([
                ('BACKGROUND', (0, 0), (0, -1), colors.lightgrey),
                ('TEXTCOLOR', (0, 0), (-1, -1), colors.black),
                ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
                ('FONTNAME', (0, 0), (0, -1), 'Helvetica-Bold'),
                ('FONTSIZE', (0, 0), (-1, -1), 10),
                ('BOTTOMPADDING', (0, 0), (-1, -1), 12),
                ('GRID', (0, 0), (-1, -1), 1, colors.black)
            ]))
            elements.append(client_table_obj)
            elements.append(Spacer(1, 0.3*inch))
            
            # Información del dispositivo
            elements.append(Paragraph("Dispositivo", self.styles['CustomSubtitle']))
            device_table = [
                ['Modelo:', device_info.get('model', 'N/A')],
                ['Marca:', device_info.get('brand', 'N/A')],
                ['Serial:', device_info.get('serial_number', 'N/A')]
            ]
            device_table_obj = Table(device_table, colWidths=[2*inch, 4*inch])
            device_table_obj.setStyle(TableStyle([
                ('BACKGROUND', (0, 0), (0, -1), colors.lightgrey),
                ('TEXTCOLOR', (0, 0), (-1, -1), colors.black),
                ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
                ('FONTNAME', (0, 0), (0, -1), 'Helvetica-Bold'),
                ('FONTSIZE', (0, 0), (-1, -1), 10),
                ('BOTTOMPADDING', (0, 0), (-1, -1), 12),
                ('GRID', (0, 0), (-1, -1), 1, colors.black)
            ]))
            elements.append(device_table_obj)
            elements.append(Spacer(1, 0.3*inch))
            
            # Servicios
            elements.append(Paragraph("Servicios", self.styles['CustomSubtitle']))
            services_data = [['Descripción', 'Cantidad', 'Precio']]
            total = 0
            for service in services:
                services_data.append([
                    service.get('description', 'N/A'),
                    str(service.get('quantity', 1)),
                    f"${service.get('price', 0):.2f}"
                ])
                total += service.get('price', 0) * service.get('quantity', 1)
            
            services_table = Table(services_data, colWidths=[3*inch, 1.5*inch, 1.5*inch])
            services_table.setStyle(TableStyle([
                ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#1a73e8')),
                ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
                ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
                ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
                ('FONTSIZE', (0, 0), (-1, -1), 9),
                ('BOTTOMPADDING', (0, 0), (-1, -1), 12),
                ('GRID', (0, 0), (-1, -1), 1, colors.black),
                ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.lightgrey])
            ]))
            elements.append(services_table)
            elements.append(Spacer(1, 0.2*inch))
            
            # Total
            total_data = [['TOTAL:', f"${total:.2f}"]]
            total_table = Table(total_data, colWidths=[4*inch, 2*inch])
            total_table.setStyle(TableStyle([
                ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor('#1a73e8')),
                ('TEXTCOLOR', (0, 0), (-1, -1), colors.whitesmoke),
                ('ALIGN', (0, 0), (-1, -1), 'RIGHT'),
                ('FONTNAME', (0, 0), (-1, -1), 'Helvetica-Bold'),
                ('FONTSIZE', (0, 0), (-1, -1), 12),
                ('BOTTOMPADDING', (0, 0), (-1, -1), 12),
                ('GRID', (0, 0), (-1, -1), 1, colors.black)
            ]))
            elements.append(total_table)
            elements.append(Spacer(1, 0.5*inch))
            
            # Firmas
            if signature_line:
                elements.append(Paragraph("_" * 30 + " " * 20 + "_" * 30, self.styles['CustomBody']))
                elements.append(Paragraph("Firma del Cliente" + " " * 28 + "Firma del Técnico", self.styles['CustomBody']))
            
            elements.append(Paragraph(f"Fecha: {datetime.now().strftime('%d/%m/%Y %H:%M')}", self.styles['CustomBody']))
            
            # Construir PDF
            doc.build(elements)
            logger.info(f"PDF de orden de reparación creado: {pdf_filename}")
            return pdf_path
            
        except Exception as e:
            logger.error(f"Error generando PDF de orden: {e}")
            return None
