import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost/CuoiKy_LTW/');
  await page.mouse.wheel(0, 500);
  await page.waitForTimeout(1000);
  await page.getByRole('link', { name: 'Đặt hàng' }).nth(1).click();
  page.once('dialog', dialog => {
    console.log(`Dialog message: ${dialog.message()}`);
    dialog.accept().catch(() => {});
  });
  await page.getByRole('button', { name: ' Thêm vào giỏ hàng' }).click();
  await page.getByRole('textbox', { name: 'Email:' }).click();
  await page.getByRole('textbox', { name: 'Email:' }).fill('binh@gmail.com');
  await page.getByRole('textbox', { name: 'Mật khẩu:' }).click();
  await page.getByRole('textbox', { name: 'Mật khẩu:' }).fill('123456');
  await page.getByRole('button', { name: 'Đăng nhập' }).click();
  await page.getByRole('link', { name: ' Tiếp tục mua sắm' }).click();
  await page.getByRole('link', { name: 'Đặt hàng' }).nth(1).click();
  await page.getByRole('button', { name: ' Thêm vào giỏ hàng' }).click();
  await page.getByRole('link', { name: '' }).click();
  await page.getByRole('button', { name: ' Thanh toán' }).click();
  await page.getByRole('textbox', { name: 'Tên người nhận *' }).click();
  await page.getByRole('textbox', { name: 'Tên người nhận *' }).fill('123');
  await page.getByRole('textbox', { name: 'Số điện thoại *' }).click();
  await page.getByRole('textbox', { name: 'Số điện thoại *' }).fill('123986672');
  await page.getByRole('textbox', { name: 'Địa chỉ giao hàng *' }).click();
  await page.getByRole('textbox', { name: 'Địa chỉ giao hàng *' }).fill('02 Võ Oanh');
  await page.getByRole('button', { name: ' Xác nhận thanh toán' }).click();
  await page.getByRole('button', { name: ' Xem' }).first().click();
  await page.getByRole('button').filter({ hasText: /^$/ }).click();
});