import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AppeasementAgent } from './appeasement-agent';

describe('AppeasementAgent', () => {
  let component: AppeasementAgent;
  let fixture: ComponentFixture<AppeasementAgent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AppeasementAgent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AppeasementAgent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
