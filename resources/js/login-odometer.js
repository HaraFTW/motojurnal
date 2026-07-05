import '../css/fuel-odometer.css';
import { initDialog } from './dialogs.js';

const INITIAL_HOLD_MS = 1000;
const ANIMATION_DURATION_MS = 4500;
const DIGIT_COUNT = 4;
const STRIP_BUFFER_SLOTS = 11;
const UPPER_DIGIT_SMOOTHING = 0.14;

class FuelOdometer {
    constructor(root, maxValue) {
        this.root = root;
        this.digits = [];

        const digitsRow = document.createElement('div');
        digitsRow.className = 'fuel-odometer__digits';

        for (let index = 0; index < DIGIT_COUNT; index += 1) {
            const digitEl = document.createElement('div');
            digitEl.className = 'fuel-odometer__digit';

            const spacer = document.createElement('span');
            spacer.className = 'fuel-odometer__spacer';
            spacer.textContent = '8';
            spacer.setAttribute('aria-hidden', 'true');

            const inner = document.createElement('div');
            inner.className = 'fuel-odometer__inner';

            const strip = document.createElement('div');
            strip.className = 'fuel-odometer__strip';

            const place = 10 ** (DIGIT_COUNT - 1 - index);
            const slotCount = Math.ceil(maxValue / place) + STRIP_BUFFER_SLOTS;

            for (let slot = 0; slot < slotCount; slot += 1) {
                const valueEl = document.createElement('span');
                valueEl.className = 'fuel-odometer__value';
                valueEl.textContent = String(slot % 10);
                strip.appendChild(valueEl);
            }

            inner.appendChild(strip);
            digitEl.appendChild(spacer);
            digitEl.appendChild(inner);
            digitsRow.appendChild(digitEl);

            this.digits.push(strip);
        }

        root.replaceChildren(digitsRow);
        this.digitPositions = [0, 0, 0, 0];
        this.setValue(0);
    }

    scrollTargetsForValue(value) {
        const clamped = Math.max(0, Math.min(9999.999, value));

        return Array.from({ length: DIGIT_COUNT }, (_, index) => {
            const place = 10 ** (DIGIT_COUNT - 1 - index);

            return wheelScrollTarget(clamped, place);
        });
    }

    applyTransforms() {
        this.digits.forEach((strip, index) => {
            strip.style.transitionDuration = '0ms';
            strip.style.transform = `translateY(calc(-1 * ${this.digitPositions[index]} * var(--fuel-odometer-slot-height)))`;
        });
    }

    setFractionalValue(value) {
        const targets = this.scrollTargetsForValue(value);

        this.digitPositions = this.digitPositions.map((current, index) => {
            if (index >= DIGIT_COUNT - 1) {
                return targets[index];
            }

            return current + (targets[index] - current) * UPPER_DIGIT_SMOOTHING;
        });

        this.applyTransforms();
    }

    setValue(value) {
        this.digitPositions = this.scrollTargetsForValue(value);
        this.applyTransforms();
    }
}

function wheelScrollTarget(value, place) {
    if (place === 1) {
        return value;
    }

    const whole = Math.floor(value / place);
    const remainder = value % place;
    const carryThreshold = place - 1;
    const lowerPlace = place / 10;

    if (remainder >= carryThreshold) {
        return whole + (remainder - carryThreshold) / lowerPlace;
    }

    return whole;
}

function easeOutCubic(progress) {
    const t = 1 - progress;

    return 1 - t * t * t;
}

function sleep(ms) {
    return new Promise((resolve) => {
        window.setTimeout(resolve, ms);
    });
}

function animateToValue(odometer, target, durationMs) {
    return new Promise((resolve) => {
        const start = performance.now();

        function frame(now) {
            const elapsed = now - start;
            const progress = Math.min(1, elapsed / durationMs);
            const easedProgress = easeOutCubic(progress);
            const value = easedProgress * target;

            odometer.setFractionalValue(value);

            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                odometer.setValue(target);
                resolve();
            }
        }

        requestAnimationFrame(frame);
    });
}

async function animateOdometer(root) {
    const target = Number.parseInt(root.dataset.count ?? '0', 10);

    if (! Number.isFinite(target) || target < 0) {
        return;
    }

    const odometer = new FuelOdometer(root, target);

    await sleep(INITIAL_HOLD_MS);

    if (target === 0) {
        return;
    }

    await animateToValue(odometer, target, ANIMATION_DURATION_MS);
}

function initLoginOdometer() {
    initDialog('login-about-open', 'login-about-dialog', 'data-login-about-close');

    const roots = document.querySelectorAll('.fuel-odometer[data-count]');

    roots.forEach((root) => {
        animateOdometer(root);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLoginOdometer);
} else {
    initLoginOdometer();
}
