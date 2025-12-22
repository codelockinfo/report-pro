import nodemailer from 'nodemailer';
import dotenv from 'dotenv';

dotenv.config();

let transporter: nodemailer.Transporter;

function getTransporter(): nodemailer.Transporter {
  if (!transporter) {
    transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST,
      port: Number(process.env.SMTP_PORT) || 587,
      secure: false,
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
  
  await mailer.sendMail({
    from: process.env.SMTP_USER,
    to,
    subject,
    text: isHtml ? undefined : content,
    html: isHtml ? content : undefined,
  });
}

