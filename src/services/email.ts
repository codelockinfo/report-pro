import nodemailer from 'nodemailer';
import dotenv from 'dotenv';

dotenv.config();

let transporter: nodemailer.Transporter;

function getTransporter(): nodemailer.Transporter {
  if (!transporter) {
    const port = Number(process.env.SMTP_PORT) || 587;
    // Port 465 uses SSL (secure: true), port 587 uses TLS (secure: false)
    const isSecure = port === 465;
    
    transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST,
      port: port,
      secure: isSecure,
      auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS,
      },
    });
  }
  return transporter;
}

export async function sendEmail(to: string, subject: string, content: string, isHtml = false) {
  const mailer = getTransporter();
  
  const fromEmail = process.env.SMTP_FROM_EMAIL || process.env.SMTP_USER;
  const fromName = process.env.SMTP_FROM_NAME || 'Report Pro';
  
  await mailer.sendMail({
    from: `"${fromName}" <${fromEmail}>`,
    to,
    subject,
    text: isHtml ? undefined : content,
    html: isHtml ? content : undefined,
  });
}

