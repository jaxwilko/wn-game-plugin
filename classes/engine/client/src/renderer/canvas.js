import Debug from '../utils/debug'
import Sprites from './sprites';

export default class Canvas
{
    constructor(target) {
        this.canvas = target;
        this.ctx = this.canvas.getContext('2d');
        this.sprites = new Sprites();
        this.layers = [
            'LAYER_BACKGROUND',
            'LAYER_BLOCKS',
            'LAYER_PROPS',
            'LAYER_TRIGGERS',
            'LAYER_MARKERS',
            'LAYER_ACTORS',
            'LAYER_SPRITES',
            'LAYER_PROPS_TOP',
        ];
    }

    resize(camera) {
        document.body.style.height = document.body.clientHeight + 'px';
        this.canvas.height = document.body.clientHeight;
        this.canvas.width = document.body.clientWidth;
        camera.size.x = this.canvas.width;
        camera.size.y = this.canvas.height;
    }

    getHeight() {
        return this.canvas.height;
    }

    getWidth() {
        return this.canvas.width;
    }

    drawBackground(colour) {
        this.ctx.fillStyle = colour || '#181818';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
    }

    renderWorld(world, camera) {
        const worldStart = {
            x: world.level.size[0][0] - camera.vector.x,
            y: world.level.size[0][1] - camera.vector.y
        };

        this.ctx.beginPath();
        this.ctx.rect(
            worldStart.x,
            worldStart.y,
            world.level.size[1][0],
            world.level.size[1][1],
        );
        this.ctx.lineCap = 'round';
        this.ctx.fillStyle = world.background || 'green';
        this.ctx.closePath();
        this.ctx.fill();

        if (Debug.get('objectPos')) {
            this.ctx.font = '12px sans';
            this.ctx.fillStyle = '#d621ea';
            this.ctx.fillText(
                `s(${worldStart.x}/${worldStart.y})    e(${worldStart.x + world.level.size[1][0]}/${worldStart.y + world.level.size[1][1]})    ${worldStart.x + world.level.size[1][0] - worldStart.x}/${worldStart.y + world.level.size[1][1] - worldStart.y}`,
                camera.size.x - 100,
                camera.size.y - 50
            );
        }

        this.layers.forEach((value, index) => {
            if (!world.layers[index]) {
                return;
            }
            world.layers[index].forEach((object) => {
                if (value === 'LAYER_ACTORS') {
                    this.renderEntity(world, object, camera);
                    return;
                }

                const x = object.vector.x - camera.vector.x;
                const y = object.vector.y - camera.vector.y;

                if (object.spriteMap && Object.entries(object.spriteMap).length) {
                    this.sprites.drawSprite(x, y, object, this.ctx);
                } else {
                    this.ctx.beginPath();
                    this.ctx.rect(
                        x,
                        y,
                        object.size.x,
                        object.size.y
                    );

                    this.ctx.lineCap = 'round';
                    this.ctx.fillStyle = object?.settings?.colour || object?.colour || 'pink';
                    this.ctx.closePath();
                    this.ctx.fill();
                }

                if (Debug.get('objectPos')) {
                    this.ctx.font = '8px sans';
                    this.ctx.fillStyle = '#b7b7b7';
                    this.ctx.fillText(`${object.vector.x}/${object.vector.y}`, x, y);
                    this.ctx.font = '8px sans';
                    this.ctx.fillStyle = '#63d7bc';
                    this.ctx.fillText(`${y}/${x}`, x, y + 10);
                }
            });
        });
    }

    renderEntity(world, entity, camera) {
        const worldStart = {
            x: world.level.size[0][0] - camera.vector.x,
            y: world.level.size[0][1] - camera.vector.y
        };

        if (entity.settings && entity.settings.name) {
            this.ctx.font = '22px Arial';
            this.ctx.fillStyle = '#1b4cba';
            this.ctx.textAlign = 'center';
            this.ctx.fillText(
                entity.settings.name,
                worldStart.x + entity.vector.x + (entity.size.x / 2),
                worldStart.y + entity.vector.y - (entity.size.y / 2) + 20
            );
        } else {
            this.ctx.font = '14px Arial';
            this.ctx.fillStyle = '#ea2132';
            this.ctx.textAlign = 'center';
            this.ctx.fillText(
                entity.health,
                worldStart.x + entity.vector.x + (entity.size.x / 2),
                worldStart.y + entity.vector.y - (entity.size.y / 2) + 15
            );
        }

        // Draw health bar
        this.ctx.beginPath();
        this.ctx.rect(
            worldStart.x + entity.vector.x,
            worldStart.y + entity.vector.y - (entity.size.y / 2) + 25,
            entity.size.x,
            2
        );
        this.ctx.closePath();
        this.ctx.lineCap = 'round';
        this.ctx.fillStyle = '#fff';
        this.ctx.fill();

        let colour = '#1b4cba';
        if (entity.health < 40) {
            colour = '#ea2132';
        } else if (entity.health < 70) {
            colour = '#ebb325';
        }

        // Draw health bar colour
        this.ctx.beginPath();
        this.ctx.rect(
            worldStart.x + entity.vector.x,
            worldStart.y + entity.vector.y - (entity.size.y / 2) + 25,
            (entity.health / 100) * entity.size.x,
            2
        );
        this.ctx.closePath();
        this.ctx.lineCap = 'round';
        this.ctx.fillStyle = colour;
        this.ctx.fill();

        if (entity.spriteMap && Object.entries(entity.spriteMap).length) {
            this.sprites.drawSprite(worldStart.x + entity.vector.x, worldStart.y + entity.vector.y, entity, this.ctx);
        } else {
            this.ctx.beginPath();
            this.ctx.rect(
                worldStart.x + entity.vector.x,
                worldStart.y + entity.vector.y,
                entity.size.x,
                entity.size.y
            );
            this.ctx.closePath();
            this.ctx.lineCap = 'round';
            this.ctx.fillStyle = entity.animation === 'attack' ? '#ea2132' : '#ffffff';
            this.ctx.fill();
        }

        if (Debug.get('objectPos')) {
            this.ctx.font = '12px sans';
            this.ctx.fillStyle = '#ea2132';
            this.ctx.fillText(
                `p(${worldStart.x + this.centerOf(entity, 'x')}/${worldStart.y + this.centerOf(entity, 'y')})`,
                camera.size.x - 100,
                camera.size.y - 100
            );
        }
    }
    renderHighlight(world, entity, camera) {
        if (typeof this.highlightOffset === 'undefined') {
            this.highlightOffset = 0;
            this.highlightTick = 0;
        }

        const worldStart = {
            x: parseInt(world.level.size[0][0]) - camera.vector.x,
            y: parseInt(world.level.size[0][1]) - camera.vector.y
        };
        this.ctx.beginPath();
        this.ctx.rect(
            worldStart.x + parseInt(entity.vector.x) - 2,
            worldStart.y + parseInt(entity.vector.y) - 2,
            parseInt(entity.size.x) + 4,
            parseInt(entity.size.y) + 4
        );
        this.ctx.closePath();
        this.ctx.strokeStyle = '#ffffff';
        this.ctx.lineWidth = 1;
        this.ctx.lineDashOffset = this.highlightOffset;
        this.ctx.setLineDash([10, 3]);
        this.ctx.stroke();

        if (++this.highlightTick > 10) {
            this.highlightOffset += 1;
            this.highlightTick = 0;
            if (this.highlightOffset > 12) {
                this.highlightOffset = 0;
            }
        }
    }

    centerOf(entity, prop) {
        return entity.vector[prop] - (entity.size[prop === 'x' ? 'width' : 'height' ] / 2);
    }

    drawMouse(mouse) {
        if (mouse.x === null || mouse.y === null) {
            return;
        }

        this.ctx.beginPath();
        this.ctx.arc(mouse.x, mouse.y, mouse.radius - 10, 0, 2 * Math.PI, false);
        this.ctx.fillStyle = '#191f24';
        this.ctx.fill();
        this.ctx.lineWidth = 10;
        this.ctx.strokeStyle = '#1d2329';
        this.ctx.stroke();
    }

    drawFps(fps) {
        if (!fps) {
            return;
        }

        if (fps.remote) {
            this.ctx.font = '36px sans';
            this.ctx.fillStyle = '#ea2132';
            this.ctx.fillText(fps.remote, this.getWidth() - (130 + (fps > 99 ? 25 : 0)), 50);
        }

        this.ctx.font = '36px sans';
        this.ctx.fillStyle = '#12bbd5';
        this.ctx.fillText(fps.local, this.getWidth() - (75 + (fps > 99 ? 25 : 0)), 50);
    }

    drawPos(player) {
        if (!player) {
            return;
        }

        this.ctx.font = '20px sans';
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fillText(`${player.vector.x}/${player.vector.y}`, this.getWidth() - 75, 80);
    }

    drawCamera(camera) {
        if (!camera) {
            return;
        }

        this.ctx.font = '20px sans';
        this.ctx.fillStyle = '#4fb534';
        this.ctx.fillText(`v${camera.vector.x}/${camera.vector.y}, s${camera.size.x}/${camera.size.y}`, this.getWidth() - 120, 110);
    }
}
