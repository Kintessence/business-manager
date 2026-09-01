<?php
declare(strict_types=1);

namespace BusinessEngine\CsvImport\Services;

final class MayaImporterService
{
    public static function importFile(string $filePath, string $type): array
    {
        if (!file_exists($filePath)) {
            return ['count' => 0, 'error' => 'Arquivo temporário não encontrado no servidor.'];
        }

        global $wpdb;

        $rawContent = file_get_contents($filePath);
        if (empty(trim($rawContent))) {
            return ['count' => 0, 'error' => 'O arquivo enviado está vazio.'];
        }

        $rawContent = preg_replace('/^\xEF\xBB\xBF/', '', $rawContent);
        
        $lines = preg_split('/\r\n|\r|\n/', trim($rawContent));
        if (empty($lines)) {
            return ['count' => 0, 'error' => 'Não foi possível ler as linhas do arquivo.'];
        }

        $firstLine = $lines[0];
        $delimiter = str_contains($firstLine, "\t") ? "\t" : (str_contains($firstLine, ';') ? ';' : ',');

        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $rawContent);
        rewind($handle);

        $header = fgetcsv($handle, 65536, $delimiter);
        if (!$header) {
            fclose($handle);
            return ['count' => 0, 'error' => 'Não foi possível interpretar o cabeçalho.'];
        }

        $header = array_map(fn($h) => trim(str_replace(['"', "'"], '', (string)$h)), $header);
        $count = 0;

        if ($type === 'orders') {
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}be_orders");
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}be_order_items");
        }

        $productsCache = [];
        $existingProds = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}be_products");
        foreach ($existingProds as $ep) {
            $productsCache[(int)$ep->id] = $ep->name;
        }

        while (($row = fgetcsv($handle, 65536, $delimiter)) !== false) {
            if (count($row) !== count($header)) {
                if (count($row) < count($header)) continue;
                $row = array_slice($row, 0, count($header));
            }

            $data = array_combine($header, $row);

            if ($type === 'products') {
                $name = sanitize_text_field($data['name'] ?? '');
                if (empty($name)) continue;

                $prodId = (int)($data['id'] ?? 0);
                $finalPrice = (float)($data['amount'] ?? $data['marketPrice'] ?? $data['price'] ?? 0.0);
                $prodTime = (int)($data['productionTime'] ?? $data['preparationTime'] ?? 0);
                $category = sanitize_text_field($data['productCategoryId'] ?? 'Geral');
                $strategicRole = (int)($data['strategicRoleId'] ?? 1);

                $wpdb->replace($wpdb->prefix . 'be_products', [
                    'id' => $prodId > 0 ? $prodId : null,
                    'name' => $name,
                    'category' => $category,
                    'strategic_role' => $strategicRole > 0 ? $strategicRole : 1,
                    'production_time_min' => $prodTime,
                    'target_margin' => 25.00,
                    'final_price' => $finalPrice,
                ]);

                $actualProdId = $prodId > 0 ? $prodId : (int)$wpdb->insert_id;
                $wpdb->delete($wpdb->prefix . 'be_product_items', ['product_id' => $actualProdId]);

                $recipesRaw = $data['meta.productsRecipes'] ?? '';
                if (!empty($recipesRaw)) {
                    $recipesRaw = str_replace("'", '"', $recipesRaw);
                    $recipesList = json_decode($recipesRaw, true);
                    if (is_array($recipesList)) {
                        foreach ($recipesList as $rItem) {
                            $rId = (int)($rItem['recipeId'] ?? 0);
                            $qty = (float)($rItem['quantity'] ?? 1.0);
                            if ($rId > 0 && $qty > 0) {
                                $wpdb->insert($wpdb->prefix . 'be_product_items', [
                                    'product_id' => $actualProdId,
                                    'item_type' => 'recipe',
                                    'item_id' => $rId,
                                    'quantity' => $qty,
                                ]);
                            }
                        }
                    }
                }

                $suppliesRaw = $data['meta.productsSupplies'] ?? '';
                if (!empty($suppliesRaw)) {
                    $suppliesRaw = str_replace("'", '"', $suppliesRaw);
                    $suppliesList = json_decode($suppliesRaw, true);
                    if (is_array($suppliesList)) {
                        foreach ($suppliesList as $sItem) {
                            $sId = (int)($sItem['supplyId'] ?? 0);
                            $qty = (float)($sItem['quantity'] ?? 1.0);
                            if ($sId > 0 && $qty > 0) {
                                $wpdb->insert($wpdb->prefix . 'be_product_items', [
                                    'product_id' => $actualProdId,
                                    'item_type' => 'supply',
                                    'item_id' => $sId,
                                    'quantity' => $qty,
                                ]);
                            }
                        }
                    }
                }

                $count++;
            } elseif ($type === 'orders') {
                $seqId = trim((string)($data['sequentialId'] ?? $data['name'] ?? $data['id'] ?? ''));

                $customerName = '';
                $candidates = [
                    $data['meta.customer.name'] ?? '',
                    $data['buyerName'] ?? '',
                    $data['customerName'] ?? '',
                ];

                foreach ($candidates as $cand) {
                    $cand = trim((string)$cand);
                    if (!empty($cand) && $cand !== 'null' && !is_numeric($cand)) {
                        $customerName = sanitize_text_field($cand);
                        break;
                    }
                }

                if (empty($customerName) && !empty($data['meta.customer'])) {
                    $metaCust = json_decode(str_replace("'", '"', (string)$data['meta.customer']), true);
                    if (is_array($metaCust) && !empty($metaCust['name'])) {
                        $customerName = sanitize_text_field((string)$metaCust['name']);
                    }
                }

                if (empty($customerName)) {
                    $customerName = !empty($seqId) ? "Pedido #{$seqId}" : "Venda Balcão / Avulsa";
                }

                $phone = '';
                $phoneCandidates = [$data['meta.customer.phone'] ?? '', $data['phone'] ?? ''];
                foreach ($phoneCandidates as $pCand) {
                    $pCand = trim((string)$pCand);
                    if (!empty($pCand) && $pCand !== 'null') {
                        $phone = sanitize_text_field($pCand);
                        break;
                    }
                }

                $total = (float)($data['amount'] ?? $data['amountPaid'] ?? $data['itemsSubtotal'] ?? 0.0);
                $paid = (float)($data['amountPaid'] ?? $total);
                $discount = (float)($data['discount'] ?? 0.0);
                $deliveryFee = (float)($data['deliveryFee'] ?? 0.0);
                $paymentStatus = sanitize_text_field($data['paymentStatus'] ?? 'paid');
                
                $deliveryAddress = sanitize_textarea_field($data['deliveryAddress'] ?? $data['meta.customer.address'] ?? '');
                
                // Mapeamento inteligente de Entrega vs Retirada
                $rawOrderType = strtolower(trim((string)($data['orderType'] ?? $data['deliveryType'] ?? '')));
                if ($rawOrderType === 'delivery' || $rawOrderType === 'entrega' || !empty($deliveryAddress) || $deliveryFee > 0) {
                    $orderType = 'entrega';
                } else {
                    $orderType = 'retirada';
                }

                $notes = sanitize_textarea_field($data['notes'] ?? '');
                $rawMotive = trim((string)($data['motive'] ?? ''));
                $orderReason = (!empty($rawMotive) && $rawMotive !== 'null') ? sanitize_text_field($rawMotive) : null;
                
                $createdAt = sanitize_text_field($data['createdAt'] ?? current_time('mysql'));
                $scheduleAt = sanitize_text_field($data['scheduleAt'] ?? $createdAt);

                $rawStatus = strtolower(trim((string)($data['status'] ?? '')));
                $productionStatus = 'entregue';

                if ($rawStatus === 'scheduled' || $rawStatus === 'agendado') {
                    $productionStatus = 'agendado';
                } elseif ($rawStatus === 'production' || $rawStatus === 'em_producao' || str_contains($rawStatus, 'produ')) {
                    $productionStatus = 'em_producao';
                } elseif ($rawStatus === 'budget' || $rawStatus === 'orcamento') {
                    $productionStatus = 'orcamento';
                } elseif ($rawStatus === 'canceled' || $rawStatus === 'cancelado') {
                    $productionStatus = 'cancelado';
                } elseif ($rawStatus === 'finalized' || $rawStatus === 'finalizado') {
                    $productionStatus = 'finalizado';
                } elseif ($rawStatus === 'delivered' || $rawStatus === 'entregue') {
                    $productionStatus = 'entregue';
                }

                $wpdb->insert($wpdb->prefix . 'be_orders', [
                    'sequential_id'     => $seqId,
                    'customer_name'     => $customerName,
                    'customer_phone'    => $phone,
                    'has_whatsapp'      => 1,
                    'items_subtotal'    => $total,
                    'discount_value'    => $discount,
                    'delivery_fee'      => $deliveryFee,
                    'total_amount'      => $total,
                    'amount_paid'       => $paid,
                    'payment_status'    => $paymentStatus,
                    'payment_method'    => 'pix',
                    'order_type'        => $orderType,
                    'production_status' => $productionStatus,
                    'order_reason'      => $orderReason,
                    'schedule_at'       => $scheduleAt,
                    'delivery_address'  => $deliveryAddress,
                    'notes'             => $notes,
                    'order_date'        => $createdAt,
                ]);

                $orderId = (int)$wpdb->insert_id;

                $localProdMap = [];
                if (!empty($data['meta.products'])) {
                    $metaProds = json_decode(str_replace("'", '"', (string)$data['meta.products']), true);
                    if (is_array($metaProds)) {
                        foreach ($metaProds as $mp) {
                            if (!empty($mp['id']) && !empty($mp['name'])) {
                                $localProdMap[(int)$mp['id']] = sanitize_text_field((string)$mp['name']);
                            }
                        }
                    }
                }

                $itemsRaw = $data['meta.ordersProducts'] ?? $data['meta.ordersItems'] ?? '';
                if (!empty($itemsRaw)) {
                    $itemsRaw = str_replace("'", '"', (string)$itemsRaw);
                    $itemsList = json_decode($itemsRaw, true);
                    if (is_array($itemsList)) {
                        foreach ($itemsList as $it) {
                            $pId = (int)($it['productId'] ?? $it['id'] ?? 0);
                            $pName = sanitize_text_field($it['name'] ?? $it['productName'] ?? '');

                            if (empty($pName)) {
                                if (isset($localProdMap[$pId])) {
                                    $pName = $localProdMap[$pId];
                                } elseif (isset($productsCache[$pId])) {
                                    $pName = $productsCache[$pId];
                                } else {
                                    $pName = $pId > 0 ? "Produto #{$pId}" : "Item";
                                }
                            }

                            $qty = (float)($it['quantity'] ?? 1.0);
                            $uPrice = (float)($it['amount'] ?? $it['price'] ?? 0.0);
                            $wpdb->insert($wpdb->prefix . 'be_order_items', [
                                'order_id'     => $orderId,
                                'product_id'   => $pId,
                                'product_name' => $pName,
                                'quantity'     => $qty,
                                'unit_price'   => $uPrice,
                                'total_price'  => $qty * $uPrice,
                            ]);
                        }
                    }
                }

                $count++;
            } elseif ($type === 'customers') {
                $name = '';
                $candNames = [$data['name'] ?? '', $data['Nome'] ?? '', $data['meta.customer.name'] ?? '', $data['buyerName'] ?? ''];
                foreach ($candNames as $c) {
                    $c = trim((string)$c);
                    if (!empty($c) && $c !== 'null') { $name = sanitize_text_field($c); break; }
                }
                if (empty($name)) continue;

                $wpdb->insert($wpdb->prefix . 'be_customers', [
                    'name'         => $name,
                    'phone'        => sanitize_text_field($data['phone'] ?? $data['meta.customer.phone'] ?? ''),
                    'has_whatsapp' => 1,
                    'email'        => sanitize_email($data['email'] ?? ''),
                    'instagram'    => sanitize_text_field($data['instagram'] ?? $data['meta.customer.instagram'] ?? ''),
                    'address'      => sanitize_textarea_field($data['address'] ?? $data['meta.customer.address'] ?? ''),
                    'birthday'     => sanitize_text_field($data['birthday'] ?? $data['meta.customer.birthday'] ?? ''),
                    'orders_count' => (int)($data['ordersCount'] ?? $data['meta.customer.ordersCount'] ?? 1),
                    'amount_spent' => (float)($data['amountSpent'] ?? $data['meta.customer.amountSpent'] ?? 0.0),
                ]);
                $count++;
            } elseif ($type === 'supplies') {
                $name = sanitize_text_field($data['name'] ?? $data['Nome'] ?? '');
                if (empty($name)) continue;

                $pkgCost = (float)($data['cost'] ?? $data['package_cost'] ?? 0.0);
                $inStock = (float)($data['inStock'] ?? $data['package_size'] ?? 1.0);
                $unitId = (int)($data['unitId'] ?? 2);
                $supplyId = (int)($data['id'] ?? 0);

                $unit = 'g'; $useUnit = 'g'; $pkgSize = $inStock > 0 ? $inStock : 1000.0;
                if ($unitId === 1) { $unit = 'un'; $useUnit = 'un'; $pkgSize = max(1.0, $inStock); }
                elseif ($unitId === 4) { $unit = 'L'; $useUnit = 'ml'; $pkgSize = max(1.0, $inStock); }
                elseif ($unitId === 5) { $unit = 'ml'; $useUnit = 'ml'; }

                $wpdb->replace($wpdb->prefix . 'be_supplies', [
                    'id'        => $supplyId > 0 ? $supplyId : null,
                    'name'      => $name,
                    'pkg_cost'  => $pkgCost > 0 ? $pkgCost : 10.0,
                    'pkg_size'  => $pkgSize,
                    'unit_type' => $unit,
                    'use_unit'  => $useUnit,
                ]);
                $count++;
            } elseif ($type === 'recipes') {
                $name = sanitize_text_field($data['name'] ?? '');
                if (empty($name)) continue;

                $recipeId = (int)($data['id'] ?? 0);
                $produce = (float)($data['estimatedProduce'] ?? 1.0);
                $unitId = (int)($data['unitId'] ?? 1);

                $wpdb->replace($wpdb->prefix . 'be_recipes', [
                    'id'            => $recipeId > 0 ? $recipeId : null,
                    'name'          => $name,
                    'yield_qty'     => $produce > 0 ? $produce : 1.0,
                    'yield_unit'    => $unitId === 2 ? 'g' : 'un',
                    'prep_time_min' => (int)($data['preparationTime'] ?? 0),
                    'bake_time_min' => 0,
                ]);

                $actualRecipeId = $recipeId > 0 ? $recipeId : (int)$wpdb->insert_id;
                $suppliesRaw = $data['meta.recipesSupplies'] ?? '';
                if (!empty($suppliesRaw)) {
                    $suppliesRaw = str_replace("'", '"', $suppliesRaw);
                    $suppliesList = json_decode($suppliesRaw, true);
                    if (is_array($suppliesList)) {
                        $wpdb->delete($wpdb->prefix . 'be_recipe_items', ['recipe_id' => $actualRecipeId]);
                        foreach ($suppliesList as $item) {
                            $sId = (int)($item['supplyId'] ?? 0);
                            $qty = (float)($item['quantity'] ?? 0.0);
                            if ($sId > 0 && $qty > 0) {
                                $wpdb->insert($wpdb->prefix . 'be_recipe_items', [
                                    'recipe_id'    => $actualRecipeId,
                                    'supply_id'    => $sId,
                                    'quantity'     => $qty,
                                    'measure_type' => 'g'
                                ]);
                            }
                        }
                    }
                }
                $count++;
            }
        }

        fclose($handle);
        return ['count' => $count, 'error' => $count === 0 ? "Nenhum registro importado." : null];
    }
}