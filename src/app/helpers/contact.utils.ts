import { Router } from "@angular/router";
import { environment } from "../../environments/environment";
import { ObrigadoService } from "./obrigado.service";

export function handleWhatsAppClick(router: Router, obrigadoService: ObrigadoService) {
  const targetUrl = environment.messageWhatsappUrl;
  window.open(targetUrl, '_blank');

  setTimeout(() => {
    obrigadoService.setType('whatsapp');
    router.navigate(['/obrigado']);
  }, environment.delayRedirect);
}

export function handleMapsClick(router: Router, obrigadoService: ObrigadoService) {
  const targetUrl = 'https://maps.app.goo.gl/g7H2EayynyVw6iVM9';
  window.open(targetUrl, '_blank');

  setTimeout(() => {
    obrigadoService.setType('maps-review');
    router.navigate(['/obrigado']);
  }, environment.delayRedirect);
}


export function handleCallClick(router: Router, obrigadoService: ObrigadoService) {
  const targetUrl = environment.callUrl;
  window.open(targetUrl, '_blank');

  setTimeout(() => {
    obrigadoService.setType('call');
    router.navigate(['/obrigado']);
  }, environment.delayRedirect);
}

export function handleEmailClick(router: Router, obrigadoService: ObrigadoService) {
  const targetUrl = environment.emailUrl;
  window.open(targetUrl, '_blank');

  setTimeout(() => {
    obrigadoService.setType('email');
    router.navigate(['/obrigado']);
  }, environment.delayRedirect);
}

export function handleBudgetFormSubmit(
  formData: { name: string, phone: string, location: string, vehicle: string, message: string },
  router: Router,
  obrigadoService: ObrigadoService
) {
  const { name, phone, location, vehicle, message } = formData;

  // Create the WhatsApp message
  const whatsappMessage = `🚨 *Pedido de Orçamento* 🚨

👤 *Nome:* ${name}
📞 *Telefone:* ${phone}
📍 *Local:* ${location}
🚗 *Veículo:* ${vehicle}

📝 *Descrição do problema:*
${message || "Não informado"}

---

✅ Aguardo seu retorno, muito obrigado! 🙏`;

  // Encode the message and create the new URL
  const encodedMessage = encodeURIComponent(whatsappMessage);
  const targetUrl = `${environment.baseWhatsappUrl}${encodedMessage}`;

  // Call the original helper function with the new URL
  window.open(targetUrl, '_blank');

  setTimeout(() => {
    obrigadoService.setType('whatsapp');
    router.navigate(['/obrigado']);
  }, environment.delayRedirect);
}