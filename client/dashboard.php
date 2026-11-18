<?php
/**
 * client/dashboard.php — Client Health Wallet & Micro-Deposit Portal
 * Wired to client/fetch_data.php & POST client/deposit_process.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$fullName       = (string) ($client['full_name'] ?? ($_SESSION['full_name'] ?? 'Client'));
$firstName      = explode(' ', trim($fullName))[0] ?: 'Client';
$policyNumber   = (string) ($client['policy_number'] ?? 'Pending');
$walletStatus   = (string) ($client['wallet_status'] ?? 'active');
$availableBal   = (float) ($client['available_balance'] ?? 0);
$totalContrib   = (float) ($client['total_contributed'] ?? 0);
$coveragePct    = $totalContrib > 0 ? min(100, max(0, (int) round(($availableBal / $totalContrib) * 100))) : 0;

$depositAmtOld  = old('amount', '2000');
$methodOld      = old('payment_method', 'MTN_MoMo');
$payerPhoneOld  = old('payer_phone', (string) ($client['phone'] ?? ''));

$extraDetail = '';
if ($latestDeposit) {
    $extraDetail = 'Ref: ' . $latestDeposit['transaction_ref']
        . ' (' . formatUGX($latestDeposit['amount']) . ' via ' . $latestDeposit['payment_method_label'] . ')';
}

uiPortalStart([
    'pageTitle'  => 'Client Portal',
    'title'      => 'Welcome, ' . $firstName,
    'subtitle'   => 'Policy ' . $policyNumber . (!empty($client['home_pharmacy']) ? ' · Home pharmacy: ' . $client['home_pharmacy'] : ''),
    'userName'   => $fullName,
    'roleLabel'  => 'Client Wallet',
    'navItems'   => [
        ['href' => 'dashboard.php',           'icon' => 'wallet',    'label' => 'Health wallet',      'active' => true],
        ['href' => '#deposit-section',        'icon' => 'plus',      'label' => 'Quick deposit',      'active' => false],
        ['href' => '#activity-section',       'icon' => 'clipboard', 'label' => 'Activity & claims',  'active' => false],
        ['href' => '#partners-section',       'icon' => 'store',     'label' => 'Partner pharmacies', 'active' => false],
    ],
]);
?>

<?= uiAlert($alert, $extraDetail) ?>

<div class="policy-banner">
    <span><?= uiIcon('shield', 18) ?></span>
    <div>
        <small>POLICY ID</small>
        <b><?= e($policyNumber) ?></b>
    </div>
    <i><?= e(strtoupper($walletStatus)) ?></i>
</div>

<section class="client-grid" id="wallet-section">
    <article class="wallet-card">
        <div class="wallet-head">
            <span><?= uiIcon('wallet') ?></span>
            <p>HEALTH WALLET</p>
            <span class="wallet-policy-pill"><?= uiIcon('shield', 13) ?> <?= e($policyNumber) ?></span>
        </div>
        <p>Available coverage balance (<?= e($client['plan_name'] ?? 'Basic Care Plan') ?>)</p>
        <?php [$ugxPrefix, $ugxNumber] = explode(' ', formatUGX($availableBal), 2); ?>
        <h2><?= e($ugxPrefix) ?> <strong><?= e($ugxNumber) ?></strong></h2>

        <div class="wallet-line">
            <span><i style="width: <?= $coveragePct ?>%"></i></span>
            <small><?= $coveragePct ?>% of savings available</small>
        </div>

        <div class="wallet-bottom">
            <div>
                <small>TOTAL CONTRIBUTED</small>
                <b><?= e(formatUGX($totalContrib)) ?></b>
            </div>
            <div>
                <small>TOTAL COVERED CLAIMS</small>
                <b><?= e(formatUGX($clientStats['total_covered'] ?? 0)) ?></b>
            </div>
        </div>
        <div class="wallet-pattern" aria-hidden="true">+</div>
    </article>

    <article class="card deposit-card" id="deposit-section">
        <div class="card-title">
            <span class="title-icon amber-bg"><?= uiIcon('plus') ?></span>
            <div>
                <h3>Quick micro-deposit</h3>
                <p>Add to your health cover anytime via mobile money or cash agent.</p>
            </div>
        </div>

        <form method="POST" action="deposit_process.php">
            <?= csrfField() ?>

            <label>
                Amount to deposit (UGX)
                <div class="currency-input">
                    <span>UGX</span>
                    <input
                        type="number"
                        id="deposit-amount"
                        name="amount"
                        min="100"
                        max="5000000"
                        step="100"
                        required
                        inputmode="numeric"
                        value="<?= $depositAmtOld ?>"
                        placeholder="2000"
                    >
                </div>
            </label>

            <div class="amount-pills" role="group" aria-label="Preset deposit amounts">
                <?php foreach ([2000, 5000, 10000, 20000] as $preset): ?>
                    <button type="button" data-deposit-amount="<?= $preset ?>">
                        <?= e(formatUGX($preset)) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="method-field-label">
                Payment method
                <div class="payment-options">
                    <?php $idx = 0; ?>
                    <?php foreach ($paymentMethods as $code => $label): ?>
                        <?php $checked = $methodOld === $code; ?>
                        <label class="payment-option <?= $checked ? 'active' : '' ?>">
                            <input
                                type="radio"
                                name="payment_method"
                                value="<?= e($code) ?>"
                                <?= $checked ? 'checked' : '' ?>
                                required
                            >
                            <span class="method-dot dot-<?= $idx ?>"></span>
                            <span><?= e($code === 'MTN_MoMo' ? 'MTN MoMo' : ($code === 'Airtel_Money' ? 'Airtel Money' : 'Cash Agent')) ?></span>
                            <span class="radio-ring" aria-hidden="true"></span>
                        </label>
                        <?php $idx++; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <label>
                Mobile money / payer phone <em>(optional — defaults to <?= e($client['phone'] ?? '') ?>)</em>
                <div class="input-wrap phone-input">
                    <span>UG</span>
                    <input
                        type="tel"
                        name="payer_phone"
                        maxlength="13"
                        inputmode="numeric"
                        pattern="(\+?256|0)7[0-9]{8}"
                        value="<?= $payerPhoneOld ?>"
                        placeholder="0701555111"
                    >
                </div>
            </label>

            <button class="btn btn-primary full" type="submit">
                Deposit micro-premium <?= uiIcon('arrow') ?>
            </button>
        </form>
    </article>
</section>

<section class="card activity-card" id="activity-section">
    <div class="activity-header">
        <div>
            <h3>Recent activity</h3>
            <p>
                <?= (int) ($clientStats['deposits_count'] ?? 0) ?> completed deposits ·
                <?= (int) ($clientStats['claims_count'] ?? 0) ?> prescription claims
                (<?= e(formatUGX($clientStats['total_copay'] ?? 0)) ?> total co-pay)
            </p>
        </div>
    </div>

    <div class="tabs activity-tabs" data-tab-group="client-activity">
        <button type="button" class="active" data-tab-target="tab-contributions">
            Contributions <span><?= count($contributions) ?></span>
        </button>
        <button type="button" data-tab-target="tab-claims">
            Prescription claims <span><?= count($claims) ?></span>
        </button>
    </div>

    <div id="tab-contributions" data-tab-panel-group="client-activity">
        <?php if (empty($contributions)): ?>
            <div class="empty-state">No contributions recorded yet. Use the Quick micro-deposit form above to top up your wallet.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Payer Phone</th>
                            <th>Reference code</th>
                            <th>Status</th>
                            <th class="right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contributions as $row): ?>
                            <tr>
                                <td data-label="Date"><?= uiDate($row['created_at']) ?></td>
                                <td data-label="Method">
                                    <span class="table-icon">
                                        <?= uiIcon($row['payment_method'] === 'Cash_Agent' ? 'user' : 'phone', 15) ?>
                                    </span>
                                    <?= e($row['payment_method_label']) ?>
                                </td>
                                <td data-label="Payer Phone"><?= e($row['payer_phone']) ?></td>
                                <td data-label="Reference"><code><?= e($row['transaction_ref']) ?></code></td>
                                <td data-label="Status"><?= uiStatusBadge($row['status']) ?></td>
                                <td data-label="Amount" class="right <?= $row['status'] === 'completed' ? 'positive' : '' ?>">
                                    <?= $row['status'] === 'completed' ? '+ ' : '' ?><?= e(formatUGX($row['amount'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div id="tab-claims" class="is-hidden" data-tab-panel-group="client-activity">
        <?php if (empty($claims)): ?>
            <div class="empty-state">No prescription claims have been made on this policy yet.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Pharmacy</th>
                            <th>Medication notes</th>
                            <th>Total cost</th>
                            <th>Covered</th>
                            <th>Co-pay</th>
                            <th class="right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claims as $row): ?>
                            <tr>
                                <td data-label="Date"><?= uiDate($row['created_at']) ?></td>
                                <td data-label="Pharmacy"><?= e($row['pharmacy_name']) ?></td>
                                <td data-label="Medication" class="td-wrap"><?= e($row['prescription_notes']) ?></td>
                                <td data-label="Total cost"><?= e(formatUGX($row['total_cost'])) ?></td>
                                <td data-label="Covered" class="positive"><?= e(formatUGX($row['covered_amount'])) ?></td>
                                <td data-label="Co-pay"><?= e(formatUGX($row['client_copay'])) ?></td>
                                <td data-label="Status" class="right"><?= uiStatusBadge($row['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($pharmacies)): ?>
    <section class="card partners-grid-card" id="partners-section">
        <h3>Partner pharmacies in Kampala</h3>
        <p>Present your Policy ID (<b><?= e($policyNumber) ?></b>) or phone number at any of these verified pharmacies.</p>
        <div class="partner-list">
            <?php foreach ($pharmacies as $ph): ?>
                <div class="partner-item">
                    <span><?= uiIcon('store') ?></span>
                    <div>
                        <b><?= e($ph['name']) ?></b>
                        <small><i></i> <?= e(strtoupper($ph['location'])) ?> · <?= e($ph['phone']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php uiPortalEnd(); ?>
